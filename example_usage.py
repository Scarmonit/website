#!/usr/bin/env python3
"""
Example Usage of Price Tracker
Demonstrates how to use the price tracker with sample products.
"""

from price_tracker import PriceTracker
import time

def main():
    print("""
    ╔══════════════════════════════════════════════╗
    ║    Price Tracker - Example Usage Demo        ║
    ╚══════════════════════════════════════════════╝
    """)
    
    # Initialize the tracker
    tracker = PriceTracker()
    
    print("\n📦 STEP 1: Adding Products to Track")
    print("-" * 50)
    
    # Example products (you should replace these with real product URLs)
    sample_products = [
        {
            "url": "https://www.amazon.com/dp/B08N5WRWNW",  # Echo Dot example
            "name": "Echo Dot (4th Gen)",
            "target_price": 30.00
        },
        {
            "url": "https://www.walmart.com/ip/Apple-AirPods-2nd-Generation/604342441",
            "name": "Apple AirPods",
            "target_price": 100.00
        },
        {
            "url": "https://www.ebay.com/itm/123456789",  # Example eBay item
            "name": "Vintage Watch",
            "target_price": 150.00
        }
    ]
    
    product_ids = []
    
    for product in sample_products:
        try:
            product_id = tracker.add_product(
                url=product["url"],
                name=product["name"],
                target_price=product["target_price"]
            )
            product_ids.append(product_id)
            print(f"✓ Added: {product['name']}")
            print(f"  ID: {product_id}")
            print(f"  Target Price: ${product['target_price']:.2f}")
            print()
        except Exception as e:
            print(f"✗ Error adding {product['name']}: {e}")
    
    print("\n💰 STEP 2: Checking Current Prices")
    print("-" * 50)
    
    for product_id in product_ids:
        print(f"Checking product ID {product_id}...")
        price = tracker.check_price(product_id)
        if price:
            print(f"  Current Price: ${price:.2f}")
        else:
            print(f"  Could not fetch price (this is normal for example URLs)")
        time.sleep(1)  # Small delay between checks
    
    print("\n📋 STEP 3: Listing All Tracked Products")
    print("-" * 50)
    
    products = tracker.list_products()
    for product in products:
        print(f"\nProduct ID: {product['id']}")
        print(f"  Name: {product['name']}")
        print(f"  URL: {product['url'][:50]}...")  # Truncate long URLs
        if product['target_price']:
            print(f"  Target Price: ${product['target_price']:.2f}")
        if product['latest_price']:
            print(f"  Latest Price: ${product['latest_price']:.2f}")
        print(f"  Last Checked: {product['last_checked'] or 'Never'}")
    
    print("\n📊 STEP 4: Viewing Price History")
    print("-" * 50)
    
    if product_ids:
        # Show history for the first product
        history = tracker.get_price_history(product_ids[0], limit=5)
        if history:
            print(f"Price history for Product ID {product_ids[0]}:")
            for record in history:
                print(f"  {record['timestamp']}: ${record['price']:.2f}")
        else:
            print("No price history available yet")
    
    print("\n" + "="*50)
    print("✅ Demo Complete!")
    print("\n📌 Next Steps:")
    print("1. Replace sample URLs with real product URLs")
    print("2. Configure email settings in config.json")
    print("3. Run 'python scheduler.py' for automated checking")
    print("4. Use 'python price_tracker.py --help' for CLI options")
    print("="*50)


if __name__ == "__main__":
    main()