#!/usr/bin/env python3
"""
Price Tracker CLI - Track product prices and get sale alerts
"""

import sys
from pathlib import Path
from typing import Optional
from datetime import datetime, timedelta

import typer
from rich.console import Console
from rich.table import Table
from rich import print as rprint

from db.models import Database, Product, PriceHistory
from services.scheduler import PriceMonitor
from services.notifier import NotificationService
from services.logger import setup_logger
from trackers import get_tracker
from utils.parsing import parse_currency

app = typer.Typer(help="Personal Price Tracker - Never miss a sale!")
console = Console()
logger = setup_logger(__name__)


@app.command()
def init_db():
    """Initialize the database with required tables"""
    try:
        db = Database()
        db.init_database()
        console.print("[green]✓ Database initialized successfully![/green]")
    except Exception as e:
        console.print(f"[red]✗ Failed to initialize database: {e}[/red]")
        raise typer.Exit(1)


@app.command()
def add(
    url: str = typer.Argument(..., help="Product URL to track"),
    target_price: float = typer.Option(..., "--target", "-t", help="Target price for alerts"),
    name: Optional[str] = typer.Option(None, "--name", "-n", help="Custom product name"),
):
    """Add a new product to track"""
    try:
        # Detect site and validate URL
        tracker = get_tracker(url)
        if not tracker:
            console.print(f"[red]✗ Unsupported site. Supported: Amazon, eBay, Walmart[/red]")
            raise typer.Exit(1)
        
        # Fetch initial price
        console.print(f"[yellow]Fetching product details...[/yellow]")
        product_info = tracker.scrape(url)
        
        if not product_info:
            console.print(f"[red]✗ Failed to fetch product information[/red]")
            raise typer.Exit(1)
        
        # Use fetched name if custom name not provided
        product_name = name or product_info.get('title', 'Unknown Product')
        current_price = product_info.get('price')
        
        # Save to database
        db = Database()
        product = Product(
            url=url,
            name=product_name,
            site=tracker.site_name,
            target_price=target_price,
            current_price=current_price
        )
        
        product_id = db.add_product(product)
        
        # Add initial price history
        if current_price:
            db.add_price_history(product_id, current_price)
        
        console.print(f"[green]✓ Added: {product_name}[/green]")
        console.print(f"  Current Price: ${current_price:.2f}" if current_price else "  Price: Unknown")
        console.print(f"  Target Price: ${target_price:.2f}")
        console.print(f"  Product ID: {product_id}")
        
    except Exception as e:
        logger.error(f"Failed to add product: {e}")
        console.print(f"[red]✗ Error: {e}[/red]")
        raise typer.Exit(1)


@app.command()
def list():
    """List all tracked products"""
    try:
        db = Database()
        products = db.get_all_products()
        
        if not products:
            console.print("[yellow]No products being tracked. Use 'add' command to start.[/yellow]")
            return
        
        table = Table(title="Tracked Products")
        table.add_column("ID", style="cyan")
        table.add_column("Name", style="white")
        table.add_column("Site", style="blue")
        table.add_column("Current", style="green")
        table.add_column("Target", style="yellow")
        table.add_column("Status", style="magenta")
        table.add_column("Last Check")
        
        for product in products:
            current = f"${product.current_price:.2f}" if product.current_price else "N/A"
            target = f"${product.target_price:.2f}"
            
            # Determine status
            if product.current_price and product.current_price <= product.target_price:
                status = "[green]ON SALE![/green]"
            elif product.current_price:
                diff = product.current_price - product.target_price
                status = f"+${diff:.2f}"
            else:
                status = "Unknown"
            
            last_check = product.last_checked or "Never"
            if isinstance(last_check, datetime):
                last_check = last_check.strftime("%Y-%m-%d %H:%M")
            
            table.add_row(
                str(product.id),
                product.name[:40] + "..." if len(product.name) > 40 else product.name,
                product.site,
                current,
                target,
                status,
                last_check
            )
        
        console.print(table)
        
    except Exception as e:
        logger.error(f"Failed to list products: {e}")
        console.print(f"[red]✗ Error: {e}[/red]")


@app.command()
def remove(product_id: int = typer.Argument(..., help="Product ID to remove")):
    """Remove a product from tracking"""
    try:
        db = Database()
        product = db.get_product(product_id)
        
        if not product:
            console.print(f"[red]✗ Product ID {product_id} not found[/red]")
            raise typer.Exit(1)
        
        if typer.confirm(f"Remove '{product.name}'?"):
            db.remove_product(product_id)
            console.print(f"[green]✓ Removed product: {product.name}[/green]")
        else:
            console.print("[yellow]Cancelled[/yellow]")
            
    except Exception as e:
        logger.error(f"Failed to remove product: {e}")
        console.print(f"[red]✗ Error: {e}[/red]")


@app.command()
def set_target(
    product_id: int = typer.Argument(..., help="Product ID"),
    target_price: float = typer.Argument(..., help="New target price"),
):
    """Update target price for a product"""
    try:
        db = Database()
        product = db.get_product(product_id)
        
        if not product:
            console.print(f"[red]✗ Product ID {product_id} not found[/red]")
            raise typer.Exit(1)
        
        old_target = product.target_price
        db.update_target_price(product_id, target_price)
        
        console.print(f"[green]✓ Updated target price for '{product.name}'[/green]")
        console.print(f"  Old target: ${old_target:.2f}")
        console.print(f"  New target: ${target_price:.2f}")
        
    except Exception as e:
        logger.error(f"Failed to update target price: {e}")
        console.print(f"[red]✗ Error: {e}[/red]")


@app.command()
def history(
    product_id: int = typer.Argument(..., help="Product ID"),
    days: int = typer.Option(30, "--days", "-d", help="Number of days to show"),
):
    """View price history for a product"""
    try:
        db = Database()
        product = db.get_product(product_id)
        
        if not product:
            console.print(f"[red]✗ Product ID {product_id} not found[/red]")
            raise typer.Exit(1)
        
        since = datetime.now() - timedelta(days=days)
        history = db.get_price_history(product_id, since)
        
        if not history:
            console.print(f"[yellow]No price history for '{product.name}'[/yellow]")
            return
        
        console.print(f"\n[bold]Price History: {product.name}[/bold]")
        console.print(f"Target Price: [yellow]${product.target_price:.2f}[/yellow]\n")
        
        table = Table()
        table.add_column("Date", style="cyan")
        table.add_column("Price", style="white")
        table.add_column("Status", style="green")
        
        for record in history:
            date_str = record.checked_at.strftime("%Y-%m-%d %H:%M")
            price_str = f"${record.price:.2f}"
            
            if record.price <= product.target_price:
                status = "[green]✓ Below target[/green]"
            else:
                diff = record.price - product.target_price
                status = f"+${diff:.2f} above"
            
            table.add_row(date_str, price_str, status)
        
        console.print(table)
        
        # Show price statistics
        prices = [h.price for h in history if h.price]
        if prices:
            console.print(f"\n[bold]Statistics:[/bold]")
            console.print(f"  Lowest:  [green]${min(prices):.2f}[/green]")
            console.print(f"  Highest: [red]${max(prices):.2f}[/red]")
            console.print(f"  Average: ${sum(prices)/len(prices):.2f}")
        
    except Exception as e:
        logger.error(f"Failed to get history: {e}")
        console.print(f"[red]✗ Error: {e}[/red]")


@app.command()
def monitor(
    once: bool = typer.Option(False, "--once", help="Run once instead of continuously"),
    interval: Optional[int] = typer.Option(None, "--interval", "-i", help="Check interval in minutes"),
):
    """Start monitoring products for price changes"""
    try:
        console.print("[bold green]Starting price monitor...[/bold green]")
        
        monitor = PriceMonitor()
        
        if once:
            console.print("[yellow]Running single check...[/yellow]")
            monitor.check_all_products()
            console.print("[green]✓ Check complete![/green]")
        else:
            console.print(f"[yellow]Monitoring continuously (Ctrl+C to stop)[/yellow]")
            monitor.start(interval_minutes=interval)
            
    except KeyboardInterrupt:
        console.print("\n[yellow]Monitoring stopped[/yellow]")
    except Exception as e:
        logger.error(f"Monitor error: {e}")
        console.print(f"[red]✗ Error: {e}[/red]")


@app.command()
def check(product_id: Optional[int] = typer.Argument(None, help="Check specific product or all")):
    """Manually check product prices"""
    try:
        monitor = PriceMonitor()
        
        if product_id:
            console.print(f"[yellow]Checking product {product_id}...[/yellow]")
            updated = monitor.check_product(product_id)
            if updated:
                console.print(f"[green]✓ Price updated: ${updated:.2f}[/green]")
            else:
                console.print(f"[red]✗ Failed to check price[/red]")
        else:
            console.print("[yellow]Checking all products...[/yellow]")
            results = monitor.check_all_products()
            console.print(f"[green]✓ Checked {results['checked']} products[/green]")
            if results['errors']:
                console.print(f"[yellow]⚠ {results['errors']} errors occurred[/yellow]")
            if results['alerts']:
                console.print(f"[green]🔔 {results['alerts']} price alerts sent![/green]")
                
    except Exception as e:
        logger.error(f"Check error: {e}")
        console.print(f"[red]✗ Error: {e}[/red]")


if __name__ == "__main__":
    app()