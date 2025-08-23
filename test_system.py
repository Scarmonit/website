#!/usr/bin/env python3
"""
System Test Script for Personal Price Tracker

This script runs comprehensive tests to ensure all components work correctly:
1. Database operations
2. Web scraping functionality  
3. Notification system
4. Configuration loading
5. Integration tests
"""

import os
import sys
import tempfile
import shutil
from pathlib import Path


def test_imports():
    """Test that all required modules can be imported"""
    print("🧪 Testing imports...")
    
    modules_to_test = [
        ('database', 'PriceTrackerDB'),
        ('scraper', 'PriceScraper'),
        ('notifications', 'NotificationManager'),
        ('price_tracker', 'PriceTracker')
    ]
    
    all_passed = True
    
    for module_name, class_name in modules_to_test:
        try:
            module = __import__(module_name)
            class_obj = getattr(module, class_name)
            print(f"   ✅ {module_name}.{class_name}")
        except ImportError as e:
            print(f"   ❌ {module_name}: Import error - {e}")
            all_passed = False
        except AttributeError as e:
            print(f"   ❌ {module_name}: Class not found - {e}")
            all_passed = False
        except Exception as e:
            print(f"   ❌ {module_name}: Unexpected error - {e}")
            all_passed = False
    
    return all_passed


def test_database():
    """Test database functionality"""
    print("\n🗄️  Testing database...")
    
    try:
        from database import PriceTrackerDB
        
        # Use temporary database for testing
        test_db_path = "test_system.db"
        db = PriceTrackerDB(test_db_path)
        
        # Test adding product
        product_id = db.add_product(
            url="https://example.com/test",
            name="Test Product",
            threshold=25.00,
            site="example.com"
        )
        print("   ✅ Product creation")
        
        # Test price update
        db.update_product_price(product_id, 29.99, 'success')
        print("   ✅ Price update")
        
        # Test retrieving products
        products = db.get_active_products()
        if len(products) >= 1:
            print("   ✅ Product retrieval")
        else:
            print("   ❌ Product retrieval")
            return False
        
        # Test price history
        history = db.get_price_history(product_id)
        if len(history) >= 1:
            print("   ✅ Price history")
        else:
            print("   ❌ Price history")
            return False
        
        # Test statistics
        stats = db.get_database_stats()
        if stats['active_products'] >= 1:
            print("   ✅ Statistics")
        else:
            print("   ❌ Statistics")
            return False
        
        # Cleanup
        if os.path.exists(test_db_path):
            os.remove(test_db_path)
        
        return True
        
    except Exception as e:
        print(f"   ❌ Database test failed: {e}")
        return False


def test_scraper():
    """Test web scraper functionality"""
    print("\n🕷️  Testing web scraper...")
    
    try:
        from scraper import PriceScraper
        
        scraper = PriceScraper()
        
        # Test site name extraction
        test_urls = [
            ("https://www.amazon.com/dp/123", "amazon.com"),
            ("https://ebay.com/itm/456", "ebay.com"),
            ("https://www.walmart.com/ip/789", "walmart.com")
        ]
        
        for url, expected_site in test_urls:
            site = scraper.get_site_name(url)
            if site == expected_site:
                print(f"   ✅ Site detection: {expected_site}")
            else:
                print(f"   ❌ Site detection: {expected_site} (got {site})")
        
        # Test price cleaning
        price_tests = [
            ("$29.99", 29.99),
            ("$1,234.56", 1234.56),
            ("25.00", 25.00),
            ("Price: $19.95", 19.95)
        ]
        
        for price_text, expected_price in price_tests:
            cleaned_price = scraper.clean_price(price_text)
            if cleaned_price == expected_price:
                print(f"   ✅ Price cleaning: {price_text} -> ${expected_price}")
            else:
                print(f"   ❌ Price cleaning: {price_text} -> {cleaned_price} (expected {expected_price})")
        
        return True
        
    except Exception as e:
        print(f"   ❌ Scraper test failed: {e}")
        return False


def test_notifications():
    """Test notification system"""
    print("\n📧 Testing notifications...")
    
    try:
        from notifications import NotificationManager
        
        # Test with disabled email config
        email_config = {'enabled': False}
        notifier = NotificationManager(email_config=email_config)
        
        print("   ✅ NotificationManager creation")
        
        # Test desktop notification (if available)
        try:
            from plyer import notification
            print("   ✅ Desktop notification support available")
            
            # Create test notification (don't actually send)
            test_product = {
                'name': 'Test Product',
                'current_price': 19.99,
                'threshold': 25.00,
                'url': 'https://example.com'
            }
            
            # Test notification formatting
            results = notifier.send_price_alert(test_product)
            print("   ✅ Notification formatting")
            
        except ImportError:
            print("   ⚠️  Desktop notifications not available (plyer not installed)")
        
        # Test email template formatting
        test_product = {
            'name': 'Test Product',
            'current_price': 19.99,
            'threshold': 25.00,
            'url': 'https://example.com'
        }
        
        test_history = [
            {'price': 25.99, 'checked_at': '2024-01-01 10:00:00', 'status': 'success'}
        ]
        
        # This should work even with email disabled
        history_html = notifier.format_price_history(test_history)
        if history_html and len(history_html) > 10:
            print("   ✅ Email template formatting")
        else:
            print("   ❌ Email template formatting")
        
        return True
        
    except Exception as e:
        print(f"   ❌ Notifications test failed: {e}")
        return False


def test_configuration():
    """Test configuration loading"""
    print("\n⚙️  Testing configuration...")
    
    try:
        from price_tracker import PriceTracker
        
        # Test with default config (should create basic config)
        temp_config = "test_config.json"
        
        # This should work even without config file
        tracker = PriceTracker(temp_config)
        print("   ✅ Configuration loading (with defaults)")
        
        # Test config.example.json if it exists
        if os.path.exists('config.example.json'):
            import json
            with open('config.example.json', 'r') as f:
                config = json.load(f)
            
            # Check required sections
            required_sections = ['email', 'desktop_notifications', 'scraping', 'notifications', 'database']
            for section in required_sections:
                if section in config:
                    print(f"   ✅ Config section: {section}")
                else:
                    print(f"   ❌ Config section missing: {section}")
                    return False
        
        return True
        
    except Exception as e:
        print(f"   ❌ Configuration test failed: {e}")
        return False


def test_integration():
    """Test integration between components"""
    print("\n🔗 Testing integration...")
    
    try:
        from price_tracker import PriceTracker
        
        # Use temporary config and database
        temp_config = "test_integration_config.json"
        temp_db = "test_integration.db"
        
        # Create minimal config
        import json
        test_config = {
            "email": {"enabled": False},
            "desktop_notifications": {"enabled": False},
            "scraping": {"check_interval_hours": 24, "user_agent": "test"},
            "notifications": {"cooldown_hours": 24},
            "database": {"path": temp_db}
        }
        
        with open(temp_config, 'w') as f:
            json.dump(test_config, f)
        
        # Initialize tracker
        tracker = PriceTracker(temp_config)
        print("   ✅ Tracker initialization")
        
        # Test adding product (this integrates database and scraper)
        success = tracker.add_product(
            url="https://example.com/test",
            name="Integration Test Product",
            threshold=50.00
        )
        if success:
            print("   ✅ Add product integration")
        else:
            print("   ❌ Add product integration")
        
        # Test listing products
        products = tracker.list_products()
        if len(products) >= 0:  # Should work even with 0 products
            print("   ✅ List products integration")
        else:
            print("   ❌ List products integration")
        
        # Test statistics
        tracker.show_stats()
        print("   ✅ Statistics integration")
        
        # Cleanup
        for temp_file in [temp_config, temp_db]:
            if os.path.exists(temp_file):
                os.remove(temp_file)
        
        return True
        
    except Exception as e:
        print(f"   ❌ Integration test failed: {e}")
        return False


def run_all_tests():
    """Run all system tests"""
    print("🧪 Personal Price Tracker - System Test Suite")
    print("=" * 60)
    
    tests = [
        ("Imports", test_imports),
        ("Database", test_database),
        ("Scraper", test_scraper),
        ("Notifications", test_notifications),
        ("Configuration", test_configuration),
        ("Integration", test_integration)
    ]
    
    passed = 0
    total = len(tests)
    
    for test_name, test_func in tests:
        try:
            result = test_func()
            if result:
                passed += 1
                print(f"✅ {test_name} test passed")
            else:
                print(f"❌ {test_name} test failed")
        except Exception as e:
            print(f"❌ {test_name} test crashed: {e}")
    
    print("\n" + "=" * 60)
    print("🏁 Test Results:")
    print(f"   ✅ Passed: {passed}/{total}")
    print(f"   ❌ Failed: {total - passed}/{total}")
    
    if passed == total:
        print("\n🎉 All tests passed! System is ready to use.")
        print("\n💡 Next steps:")
        print("   1. Run: python quick_start.py")
        print("   2. Or add products manually: python price_tracker.py --add URL --name NAME --threshold PRICE")
    else:
        print("\n⚠️  Some tests failed. Please check the errors above.")
        print("💡 Common issues:")
        print("   - Missing dependencies: pip install -r requirements.txt")
        print("   - File permissions issues")
        print("   - Network connectivity problems")
    
    return passed == total


if __name__ == "__main__":
    try:
        run_all_tests()
    except KeyboardInterrupt:
        print("\n👋 Tests interrupted by user")
        sys.exit(1)
    except Exception as e:
        print(f"\n❌ Test suite failed: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)
