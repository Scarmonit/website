#!/usr/bin/env python3
"""
Personal Price Tracker
A tool to monitor product prices from e-commerce websites and notify when prices drop.
"""

import sqlite3
import requests
from bs4 import BeautifulSoup
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from datetime import datetime
import json
import time
import re
from typing import Dict, Optional, Tuple, List
import logging
from urllib.parse import urlparse

# Set up logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


class PriceTracker:
    """Main class for tracking product prices across different e-commerce sites."""
    
    def __init__(self, db_path: str = "price_history.db", config_path: str = "config.json"):
        """
        Initialize the price tracker.
        
        Args:
            db_path: Path to SQLite database file
            config_path: Path to configuration file
        """
        self.db_path = db_path
        self.config_path = config_path
        self.config = self.load_config()
        self.init_database()
        
        # Headers to avoid bot detection
        self.headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept-Language': 'en-US,en;q=0.9',
            'Accept-Encoding': 'gzip, deflate, br',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Connection': 'keep-alive',
            'Upgrade-Insecure-Requests': '1'
        }
    
    def load_config(self) -> Dict:
        """Load configuration from JSON file."""
        try:
            with open(self.config_path, 'r') as f:
                return json.load(f)
        except FileNotFoundError:
            logger.warning(f"Config file {self.config_path} not found. Using defaults.")
            return {
                "email": {
                    "enabled": False,
                    "smtp_server": "",
                    "smtp_port": 587,
                    "sender_email": "",
                    "sender_password": "",
                    "recipient_email": ""
                },
                "check_interval_hours": 24
            }
    
    def init_database(self):
        """Initialize SQLite database with required tables."""
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        # Create products table
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                url TEXT UNIQUE NOT NULL,
                name TEXT,
                target_price REAL,
                last_checked TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ''')
        
        # Create price history table
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS price_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER,
                price REAL,
                currency TEXT,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products (id)
            )
        ''')
        
        conn.commit()
        conn.close()
        logger.info("Database initialized successfully")
    
    def add_product(self, url: str, name: str = None, target_price: float = None) -> int:
        """
        Add a new product to track.
        
        Args:
            url: Product URL
            name: Product name (optional)
            target_price: Target price for notifications
            
        Returns:
            Product ID
        """
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        try:
            cursor.execute('''
                INSERT INTO products (url, name, target_price)
                VALUES (?, ?, ?)
            ''', (url, name, target_price))
            
            product_id = cursor.lastrowid
            conn.commit()
            logger.info(f"Added product: {name or url}")
            return product_id
            
        except sqlite3.IntegrityError:
            # Product already exists, get its ID
            cursor.execute('SELECT id FROM products WHERE url = ?', (url,))
            product_id = cursor.fetchone()[0]
            
            # Update target price if provided
            if target_price is not None:
                cursor.execute('''
                    UPDATE products SET target_price = ? WHERE id = ?
                ''', (target_price, product_id))
                conn.commit()
            
            logger.info(f"Product already exists: {name or url}")
            return product_id
            
        finally:
            conn.close()
    
    def get_price_from_page(self, url: str) -> Tuple[Optional[float], Optional[str]]:
        """
        Extract price from product page.
        
        Args:
            url: Product URL
            
        Returns:
            Tuple of (price, currency) or (None, None) if extraction fails
        """
        try:
            response = requests.get(url, headers=self.headers, timeout=10)
            response.raise_for_status()
            soup = BeautifulSoup(response.content, 'html.parser')
            
            domain = urlparse(url).netloc.lower()
            
            # Amazon price selectors
            if 'amazon' in domain:
                price_selectors = [
                    'span.a-price-whole',
                    'span#priceblock_dealprice',
                    'span#priceblock_ourprice',
                    'span.a-price.a-text-price.a-size-medium.apexPriceToPay',
                    'span.a-price-range',
                    'span.a-price.a-text-price.header-price'
                ]
                
                for selector in price_selectors:
                    price_elem = soup.select_one(selector)
                    if price_elem:
                        price_text = price_elem.get_text().strip()
                        price = self.extract_price_from_text(price_text)
                        if price:
                            return price, 'USD'
            
            # eBay price selectors
            elif 'ebay' in domain:
                price_selectors = [
                    'span.ux-textspans--BOLD',
                    'div.x-price-primary span',
                    'span[itemprop="price"]',
                    'div.vi-VR-cvipPrice'
                ]
                
                for selector in price_selectors:
                    price_elem = soup.select_one(selector)
                    if price_elem:
                        price_text = price_elem.get_text().strip()
                        price = self.extract_price_from_text(price_text)
                        if price:
                            return price, 'USD'
            
            # Walmart price selectors
            elif 'walmart' in domain:
                price_selectors = [
                    'span[itemprop="price"]',
                    'span.price-characteristic',
                    'div[data-testid="price-wrap"] span',
                    'span.w_VM'
                ]
                
                for selector in price_selectors:
                    price_elem = soup.select_one(selector)
                    if price_elem:
                        price_text = price_elem.get_text().strip()
                        price = self.extract_price_from_text(price_text)
                        if price:
                            return price, 'USD'
            
            # Generic price extraction for other sites
            else:
                # Look for common price patterns in the HTML
                price_patterns = [
                    r'\$\s*(\d+(?:,\d{3})*(?:\.\d{2})?)',
                    r'USD\s*(\d+(?:,\d{3})*(?:\.\d{2})?)',
                    r'Price:\s*\$?\s*(\d+(?:,\d{3})*(?:\.\d{2})?)'
                ]
                
                page_text = soup.get_text()
                for pattern in price_patterns:
                    match = re.search(pattern, page_text)
                    if match:
                        price_str = match.group(1).replace(',', '')
                        try:
                            return float(price_str), 'USD'
                        except ValueError:
                            continue
            
            logger.warning(f"Could not extract price from {domain}")
            return None, None
            
        except requests.RequestException as e:
            logger.error(f"Error fetching page {url}: {e}")
            return None, None
        except Exception as e:
            logger.error(f"Unexpected error extracting price from {url}: {e}")
            return None, None
    
    def extract_price_from_text(self, text: str) -> Optional[float]:
        """
        Extract numeric price from text string.
        
        Args:
            text: Text containing price
            
        Returns:
            Price as float or None
        """
        # Remove currency symbols and extra spaces
        text = re.sub(r'[^\d.,]', '', text)
        text = text.replace(',', '')
        
        # Try to extract the price
        try:
            # Handle price ranges (take the first price)
            if '-' in text:
                text = text.split('-')[0]
            
            price = float(text)
            return price if price > 0 else None
        except ValueError:
            return None
    
    def check_price(self, product_id: int) -> Optional[float]:
        """
        Check current price for a product.
        
        Args:
            product_id: Database product ID
            
        Returns:
            Current price or None if check fails
        """
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        # Get product details
        cursor.execute('SELECT url, name, target_price FROM products WHERE id = ?', (product_id,))
        product = cursor.fetchone()
        
        if not product:
            logger.error(f"Product {product_id} not found")
            conn.close()
            return None
        
        url, name, target_price = product
        
        # Get current price
        price, currency = self.get_price_from_page(url)
        
        if price is not None:
            # Store price in history
            cursor.execute('''
                INSERT INTO price_history (product_id, price, currency)
                VALUES (?, ?, ?)
            ''', (product_id, price, currency or 'USD'))
            
            # Update last checked timestamp
            cursor.execute('''
                UPDATE products SET last_checked = CURRENT_TIMESTAMP
                WHERE id = ?
            ''', (product_id,))
            
            conn.commit()
            logger.info(f"Price for {name or url}: ${price:.2f}")
            
            # Check if price dropped below target
            if target_price and price < target_price:
                self.send_notification(name or url, price, target_price, url)
            
        else:
            logger.warning(f"Could not get price for {name or url}")
        
        conn.close()
        return price
    
    def check_all_products(self):
        """Check prices for all tracked products."""
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        cursor.execute('SELECT id FROM products')
        products = cursor.fetchall()
        conn.close()
        
        logger.info(f"Checking {len(products)} products...")
        
        for (product_id,) in products:
            self.check_price(product_id)
            # Small delay to avoid rate limiting
            time.sleep(2)
        
        logger.info("Price check complete")
    
    def send_notification(self, product_name: str, current_price: float, 
                         target_price: float, url: str):
        """
        Send notification when price drops below target.
        
        Args:
            product_name: Name of the product
            current_price: Current price
            target_price: Target price threshold
            url: Product URL
        """
        message = f"""
        🎉 Price Drop Alert! 🎉
        
        Product: {product_name}
        Current Price: ${current_price:.2f}
        Target Price: ${target_price:.2f}
        Savings: ${target_price - current_price:.2f}
        
        Link: {url}
        """
        
        logger.info(f"PRICE ALERT: {product_name} is now ${current_price:.2f} (target: ${target_price:.2f})")
        
        # Send email if configured
        if self.config.get('email', {}).get('enabled'):
            self.send_email_notification(product_name, message)
        
        # Print to console (can be replaced with desktop notification)
        print("\n" + "="*50)
        print(message)
        print("="*50 + "\n")
    
    def send_email_notification(self, subject: str, body: str):
        """
        Send email notification.
        
        Args:
            subject: Email subject
            body: Email body
        """
        email_config = self.config.get('email', {})
        
        if not all([email_config.get('smtp_server'), 
                   email_config.get('sender_email'),
                   email_config.get('sender_password'),
                   email_config.get('recipient_email')]):
            logger.warning("Email configuration incomplete")
            return
        
        try:
            msg = MIMEMultipart()
            msg['From'] = email_config['sender_email']
            msg['To'] = email_config['recipient_email']
            msg['Subject'] = f"Price Alert: {subject}"
            
            msg.attach(MIMEText(body, 'plain'))
            
            with smtplib.SMTP(email_config['smtp_server'], email_config['smtp_port']) as server:
                server.starttls()
                server.login(email_config['sender_email'], email_config['sender_password'])
                server.send_message(msg)
            
            logger.info("Email notification sent successfully")
            
        except Exception as e:
            logger.error(f"Failed to send email: {e}")
    
    def get_price_history(self, product_id: int, limit: int = 30) -> List[Dict]:
        """
        Get price history for a product.
        
        Args:
            product_id: Product ID
            limit: Number of records to return
            
        Returns:
            List of price history records
        """
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        cursor.execute('''
            SELECT price, currency, timestamp
            FROM price_history
            WHERE product_id = ?
            ORDER BY timestamp DESC
            LIMIT ?
        ''', (product_id, limit))
        
        history = []
        for price, currency, timestamp in cursor.fetchall():
            history.append({
                'price': price,
                'currency': currency,
                'timestamp': timestamp
            })
        
        conn.close()
        return history
    
    def list_products(self) -> List[Dict]:
        """
        List all tracked products.
        
        Returns:
            List of product dictionaries
        """
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        cursor.execute('''
            SELECT p.id, p.url, p.name, p.target_price, p.last_checked,
                   ph.price as latest_price
            FROM products p
            LEFT JOIN (
                SELECT product_id, price, 
                       ROW_NUMBER() OVER (PARTITION BY product_id ORDER BY timestamp DESC) as rn
                FROM price_history
            ) ph ON p.id = ph.product_id AND ph.rn = 1
        ''')
        
        products = []
        for row in cursor.fetchall():
            products.append({
                'id': row[0],
                'url': row[1],
                'name': row[2],
                'target_price': row[3],
                'last_checked': row[4],
                'latest_price': row[5]
            })
        
        conn.close()
        return products


def main():
    """Main function for command-line usage."""
    import argparse
    
    parser = argparse.ArgumentParser(description='Personal Price Tracker')
    parser.add_argument('action', choices=['add', 'check', 'list', 'history'],
                       help='Action to perform')
    parser.add_argument('--url', help='Product URL')
    parser.add_argument('--name', help='Product name')
    parser.add_argument('--target', type=float, help='Target price')
    parser.add_argument('--id', type=int, help='Product ID')
    
    args = parser.parse_args()
    
    tracker = PriceTracker()
    
    if args.action == 'add':
        if not args.url:
            print("Error: URL is required for adding a product")
            return
        
        product_id = tracker.add_product(args.url, args.name, args.target)
        print(f"Product added with ID: {product_id}")
        
        # Check price immediately
        price = tracker.check_price(product_id)
        if price:
            print(f"Current price: ${price:.2f}")
    
    elif args.action == 'check':
        if args.id:
            tracker.check_price(args.id)
        else:
            tracker.check_all_products()
    
    elif args.action == 'list':
        products = tracker.list_products()
        if not products:
            print("No products being tracked")
        else:
            print("\nTracked Products:")
            print("-" * 80)
            for p in products:
                print(f"ID: {p['id']}")
                print(f"Name: {p['name'] or 'N/A'}")
                print(f"URL: {p['url']}")
                print(f"Target Price: ${p['target_price']:.2f}" if p['target_price'] else "Target Price: Not set")
                print(f"Latest Price: ${p['latest_price']:.2f}" if p['latest_price'] else "Latest Price: Not checked")
                print(f"Last Checked: {p['last_checked'] or 'Never'}")
                print("-" * 80)
    
    elif args.action == 'history':
        if not args.id:
            print("Error: Product ID is required for history")
            return
        
        history = tracker.get_price_history(args.id)
        if not history:
            print("No price history available")
        else:
            print("\nPrice History:")
            print("-" * 40)
            for record in history:
                print(f"{record['timestamp']}: ${record['price']:.2f} {record['currency']}")


if __name__ == "__main__":
    main()