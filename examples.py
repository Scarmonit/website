#!/usr/bin/env python3
"""
Examples and Demo for Personal Price Tracker

This file demonstrates how to use the price tracker programmatically
and provides example usage patterns.
"""

from price_tracker import PriceTracker
from database import PriceTrackerDB
from scraper import PriceScraper
from notifications import NotificationManager
import json
import time


def demo_basic_usage():
    """Demonstrate basic price tracker usage"""
    print("🚀 Personal Price Tracker - Basic Usage Demo")
    print("=" * 60)
    
    # Initialize tracker
    tracker = PriceTracker()
    
    # Example products (these are sample URLs - replace with real ones)
    example_products = [
        {
            "url": "https://www.amazon.com/dp/B08N5WRWNW",
            "name": "Echo Dot (4th Gen)",
            "threshold": 30.00
        },
        {
            "url": "https://www.walmart.com/ip/555172369",
            "name": "Bluetooth Speaker",
            "threshold": 25.00
        },
        {
            "url": "https://www.ebay.com/itm/123456789",
            "name": "Used iPhone",
            "threshold": 200.00
        }
    ]
    
    print("📦 Adding example products...")
    
    for product in example_products:
        print(f"\n➕ Adding: {product['name']}")
        success = tracker.add_product(
            url=product['url'],
            name=product['name'],
            threshold=product['threshold']
        )
        
        if success:
            print(f"✅ Successfully added: {product['name']}")
        else:
            print(f"❌ Failed to add: {product['name']}")
    
    print("\n📋 Listing all tracked products:")
    products = tracker.list_products()
    
    print("\n🔍 Running price check...")
    results = tracker.check_prices(verbose=True)
    
    print(f"\n📊 Final Results:")
    print(f"   Products added: {len(example_products)}")
    print(f"   Price checks: {results['checked']}")
    print(f"   Alerts sent: {results['alerts']}")
    print(f"   Errors: {results['errors']}")


def demo_database_operations():
    """Demonstrate database operations"""
    print("\n🗄️  Database Operations Demo")
    print("=" * 40)
    
    # Initialize database
    db = PriceTrackerDB("demo_tracker.db")
    
    # Add sample product
    product_id = db.add_product(
        url="https://example.com/product",
        name="Demo Product",
        threshold=50.00,
        site="example.com"
    )
    
    print(f"📦 Added product with ID: {product_id}")
    
    # Simulate price updates
    prices = [59.99, 55.00, 48.99, 45.00]  # Decreasing prices
    
    for price in prices:
        db.update_product_price(product_id, price, 'success')
        print(f"💰 Updated price to: ${price}")
        time.sleep(0.1)  # Small delay for timestamp differences
    
    # Get price history
    history = db.get_price_history(product_id)
    print(f"\n📊 Price History ({len(history)} records):")
    
    for record in history:
        if record['status'] == 'success':
            print(f"   {record['checked_at'][:19]}: ${record['price']:.2f}")
    
    # Get statistics
    stats = db.get_database_stats()
    print(f"\n📈 Database Stats:")
    for key, value in stats.items():
        print(f"   {key}: {value}")
    
    # Cleanup
    import os
    if os.path.exists("demo_tracker.db"):
        os.remove("demo_tracker.db")
        print("\n🗑️  Demo database cleaned up")


def demo_scraper_testing():
    """Demonstrate scraper testing capabilities"""
    print("\n🕷️  Web Scraper Testing Demo")
    print("=" * 40)
    
    scraper = PriceScraper()
    
    # Test URLs (replace with real product URLs for actual testing)
    test_urls = [
        "https://www.amazon.com/dp/B08N5WRWNW",
        "https://www.walmart.com/ip/555172369",
        "https://www.ebay.com/itm/123456789"
    ]
    
    for url in test_urls:
        print(f"\n🔍 Testing URL: {url}")
        site = scraper.get_site_name(url)
        print(f"🌐 Detected site: {site}")
        
        # Test selector analysis
        print("🧪 Running selector tests...")
        results = scraper.test_selectors(url)
        
        print(f"   Selectors tested: {results['selectors_tested']}")
        print(f"   Prices found: {len(results['prices_found'])}")
        
        if results['prices_found']:
            print("   💰 Found prices:")
            for price_data in results['prices_found'][:3]:  # Show first 3
                print(f"      ${price_data['price']:.2f} (using {price_data['selector']})")
        
        if results['errors']:
            print(f"   ⚠️  Errors: {len(results['errors'])}")


def demo_notification_system():
    """Demonstrate notification system"""
    print("\n📧 Notification System Demo")
    print("=" * 40)
    
    # Example email configuration (disabled for demo)
    email_config = {
        'enabled': False,  # Set to True with real credentials to test
        'smtp_server': 'smtp.gmail.com',
        'smtp_port': 587,
        'sender_email': 'your-email@gmail.com',
        'sender_password': 'your-app-password',
        'recipient_email': 'your-email@gmail.com'
    }
    
    # Initialize notification manager
    notifier = NotificationManager(
        email_config=email_config,
        desktop_enabled=True
    )
    
    # Sample product data for testing
    sample_product = {
        'id': 1,
        'name': 'Gaming Mechanical Keyboard',
        'current_price': 45.99,
        'threshold': 50.00,
        'url': 'https://example.com/keyboard'
    }
    
    sample_history = [
        {'price': 65.99, 'checked_at': '2024-01-10 10:00:00', 'status': 'success'},
        {'price': 59.99, 'checked_at': '2024-01-11 10:00:00', 'status': 'success'},
        {'price': 54.99, 'checked_at': '2024-01-12 10:00:00', 'status': 'success'},
        {'price': 45.99, 'checked_at': '2024-01-13 10:00:00', 'status': 'success'},
    ]
    
    print("🧪 Testing notification system...")
    
    # Test desktop notification
    print("\n🖥️  Testing desktop notification...")
    desktop_result = notifier.send_desktop_notification(sample_product)
    print(f"   Result: {'✅ Success' if desktop_result else '❌ Failed'}")
    
    # Test email notification (if enabled)
    if email_config.get('enabled'):
        print("\n📧 Testing email notification...")
        email_result = notifier.send_email_notification(sample_product, sample_history)
        print(f"   Result: {'✅ Success' if email_result else '❌ Failed'}")
    else:
        print("\n📧 Email notifications disabled (configure email_config to test)")
    
    # Test full alert system
    print("\n🚨 Testing complete alert system...")
    results = notifier.send_price_alert(sample_product, sample_history)
    
    print("   Alert Results:")
    for alert_type, success in results.items():
        status = "✅ Sent" if success else "❌ Failed"
        print(f"      {alert_type}: {status}")


def create_sample_config():
    """Create a sample configuration file"""
    print("\n⚙️  Creating Sample Configuration")
    print("=" * 40)
    
    sample_config = {
        "email": {
            "enabled": False,
            "smtp_server": "smtp.gmail.com",
            "smtp_port": 587,
            "sender_email": "your-email@gmail.com",
            "sender_password": "your-app-password",
            "recipient_email": "your-email@gmail.com"
        },
        "desktop_notifications": {
            "enabled": True,
            "timeout": 10
        },
        "scraping": {
            "check_interval_hours": 24,
            "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
            "request_timeout": 10,
            "max_retries": 3,
            "delay_between_requests": 2
        },
        "notifications": {
            "cooldown_hours": 24,
            "max_notifications_per_day": 10
        },
        "database": {
            "path": "price_tracker.db",
            "cleanup_days": 90
        },
        "logging": {
            "level": "INFO",
            "file": "price_tracker.log",
            "max_size_mb": 10
        }
    }
    
    filename = "config.demo.json"
    with open(filename, 'w') as f:
        json.dump(sample_config, f, indent=2)
    
    print(f"✅ Created sample configuration: {filename}")
    print("💡 Copy this to 'config.json' and customize for your needs")


def main():
    """Run all demos"""
    print("🎮 Personal Price Tracker - Complete Demo Suite")
    print("=" * 80)
    
    try:
        # Run individual demos
        demo_database_operations()
        demo_scraper_testing() 
        demo_notification_system()
        create_sample_config()
        
        print("\n" + "=" * 80)
        print("🎉 Demo suite completed!")
        print("\n💡 Next Steps:")
        print("   1. Configure config.json with your email settings")
        print("   2. Add real product URLs with: python price_tracker.py --add URL --name NAME --threshold PRICE")
        print("   3. Run price checks with: python price_tracker.py --check")
        print("   4. Set up automatic monitoring with: python price_tracker.py --daemon")
        
    except Exception as e:
        print(f"❌ Demo failed: {e}")
        import traceback
        traceback.print_exc()


if __name__ == "__main__":
    main()
