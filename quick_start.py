#!/usr/bin/env python3
"""
Quick Start Script for Personal Price Tracker

This script helps new users get started quickly by:
1. Checking dependencies
2. Creating initial configuration  
3. Initializing database
4. Adding sample products (optional)
5. Running first price check
"""

import os
import sys
import json
import subprocess
from pathlib import Path


def check_dependencies():
    """Check if required Python packages are installed"""
    print("🔍 Checking dependencies...")
    
    required_packages = [
        'requests',
        'beautifulsoup4', 
        'lxml',
        'schedule',
        'plyer'
    ]
    
    missing_packages = []
    
    for package in required_packages:
        try:
            # Handle special package name mappings
            if package == 'beautifulsoup4':
                import bs4
            else:
                __import__(package)
            print(f"   ✅ {package}")
        except ImportError:
            print(f"   ❌ {package} - MISSING")
            missing_packages.append(package)
        except Exception as e:
            # Handle other import errors gracefully
            print(f"   ⚠️  {package} - Error: {e}")
            missing_packages.append(package)
    
    if missing_packages:
        print(f"\n❌ Missing packages: {', '.join(missing_packages)}")
        print("💡 Install with: pip install -r requirements.txt")
        
        # Offer to install automatically
        response = input("\n🤔 Install missing packages automatically? (y/n): ").lower()
        if response == 'y':
            try:
                print("📦 Installing packages...")
                subprocess.check_call([sys.executable, '-m', 'pip', 'install'] + missing_packages)
                print("✅ Packages installed successfully!")
                return True
            except subprocess.CalledProcessError:
                print("❌ Failed to install packages automatically")
                print("💡 Please run: pip install -r requirements.txt")
                return False
        else:
            return False
    
    print("✅ All dependencies satisfied!")
    return True


def create_config():
    """Create configuration file if it doesn't exist"""
    print("\n⚙️  Setting up configuration...")
    
    if os.path.exists('config.json'):
        print("   ✅ config.json already exists")
        return True
    
    if not os.path.exists('config.example.json'):
        print("   ❌ config.example.json not found")
        return False
    
    # Copy example config
    try:
        import shutil
        shutil.copy2('config.example.json', 'config.json')
        print("   ✅ Created config.json from example")
        
        # Ask if user wants to configure email
        print("\n📧 Email Notification Setup (Optional)")
        setup_email = input("   🤔 Set up email notifications now? (y/n): ").lower()
        
        if setup_email == 'y':
            configure_email()
        else:
            print("   💡 You can configure email later by editing config.json")
        
        return True
        
    except Exception as e:
        print(f"   ❌ Failed to create config.json: {e}")
        return False


def configure_email():
    """Interactive email configuration"""
    print("\n   📧 Email Configuration:")
    print("   💡 For Gmail, you need to:")
    print("      1. Enable 2-factor authentication")  
    print("      2. Generate an 'App Password' (not your regular password)")
    print("      3. Use the app password here")
    
    email = input("   📧 Your email address: ").strip()
    password = input("   🔑 App password (hidden): ").strip()
    
    if email and password:
        try:
            with open('config.json', 'r') as f:
                config = json.load(f)
            
            config['email']['enabled'] = True
            config['email']['sender_email'] = email
            config['email']['sender_password'] = password
            config['email']['recipient_email'] = email
            
            with open('config.json', 'w') as f:
                json.dump(config, f, indent=2)
            
            print("   ✅ Email configuration saved!")
            
        except Exception as e:
            print(f"   ❌ Failed to save email config: {e}")
    else:
        print("   💡 Skipping email configuration")


def initialize_database():
    """Initialize the database"""
    print("\n🗄️  Initializing database...")
    
    try:
        from database import PriceTrackerDB
        db = PriceTrackerDB()
        print("   ✅ Database initialized successfully!")
        return True
    except Exception as e:
        print(f"   ❌ Database initialization failed: {e}")
        return False


def add_sample_products():
    """Offer to add sample products for testing"""
    print("\n📦 Sample Products Setup")
    print("   💡 Want to add some sample products for testing?")
    print("   ⚠️  Note: These are example URLs and may not work for actual price checking")
    
    add_samples = input("   🤔 Add sample products? (y/n): ").lower()
    
    if add_samples != 'y':
        print("   💡 Skipped sample products. Add your own with:")
        print("      python price_tracker.py --add URL --name 'Product Name' --threshold 50.00")
        return
    
    sample_products = [
        {
            "url": "https://www.amazon.com/dp/B08N5WRWNW", 
            "name": "Echo Dot (4th Gen) - Sample",
            "threshold": 30.00
        },
        {
            "url": "https://www.walmart.com/ip/555172369",
            "name": "Bluetooth Speaker - Sample", 
            "threshold": 25.00
        }
    ]
    
    try:
        from price_tracker import PriceTracker
        tracker = PriceTracker()
        
        for product in sample_products:
            print(f"   ➕ Adding: {product['name']}")
            success = tracker.add_product(
                product['url'],
                product['name'], 
                product['threshold']
            )
            
            if success:
                print(f"      ✅ Added successfully")
            else:
                print(f"      ❌ Failed to add")
        
        return True
        
    except Exception as e:
        print(f"   ❌ Failed to add sample products: {e}")
        return False


def run_test_check():
    """Run a test price check"""
    print("\n🔍 Test Price Check")
    
    run_test = input("   🤔 Run a test price check now? (y/n): ").lower()
    
    if run_test != 'y':
        print("   💡 Skipped test check. Run manually with:")
        print("      python price_tracker.py --check")
        return
    
    try:
        from price_tracker import PriceTracker
        tracker = PriceTracker()
        
        print("   🔍 Running price check...")
        results = tracker.check_prices(verbose=False)
        
        print(f"   📊 Results:")
        print(f"      ✅ Checked: {results['checked']}")
        print(f"      🚨 Alerts: {results['alerts']}")  
        print(f"      ❌ Errors: {results['errors']}")
        
        return True
        
    except Exception as e:
        print(f"   ❌ Test check failed: {e}")
        return False


def show_next_steps():
    """Show next steps for the user"""
    print("\n" + "=" * 60)
    print("🎉 Quick Start Complete!")
    print("=" * 60)
    
    print("\n📚 What's Next?")
    print("\n1. 📦 Add Your Products:")
    print("   python price_tracker.py --add 'PRODUCT_URL' --name 'Product Name' --threshold 50.00")
    
    print("\n2. 🔍 Check Prices:")
    print("   python price_tracker.py --check")
    
    print("\n3. 📋 List Products:")
    print("   python price_tracker.py --list")
    
    print("\n4. 📊 View History:")
    print("   python price_tracker.py --history --id 1")
    
    print("\n5. 🤖 Auto Monitoring:")
    print("   python price_tracker.py --daemon")
    
    print("\n📧 Email Setup:")
    if os.path.exists('config.json'):
        try:
            with open('config.json', 'r') as f:
                config = json.load(f)
            if config.get('email', {}).get('enabled'):
                print("   ✅ Email notifications are configured")
                print("   🧪 Test with: python price_tracker.py --test-email")
            else:
                print("   ⚙️  Configure email in config.json for notifications")
        except:
            print("   ⚙️  Configure email in config.json for notifications")
    
    print("\n🔧 Configuration:")
    print("   📝 Edit config.json to customize settings")
    print("   🖥️  Desktop notifications: Enabled by default")
    
    print("\n📖 Documentation:")
    print("   📘 Full guide: README.md")
    print("   🧪 Examples: python examples.py")
    
    print("\n❓ Need Help?")
    print("   python price_tracker.py --help")


def main():
    """Run the quick start process"""
    print("🚀 Personal Price Tracker - Quick Start Setup")
    print("=" * 60)
    print("This script will help you get started in a few minutes!\n")
    
    # Check Python version
    if sys.version_info < (3, 7):
        print("❌ Python 3.7 or higher is required")
        print(f"   Current version: {sys.version}")
        return False
    
    print(f"✅ Python version: {sys.version.split()[0]}")
    
    # Run setup steps
    steps = [
        ("Dependencies", check_dependencies),
        ("Configuration", create_config), 
        ("Database", initialize_database),
        ("Sample Products", add_sample_products),
        ("Test Check", run_test_check)
    ]
    
    for step_name, step_func in steps:
        try:
            result = step_func()
            if not result:
                print(f"\n⚠️  {step_name} step had issues, but continuing...")
        except Exception as e:
            print(f"\n❌ {step_name} step failed: {e}")
            print("Continuing with remaining steps...")
    
    show_next_steps()
    return True


if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\n\n👋 Setup interrupted by user")
        sys.exit(1)
    except Exception as e:
        print(f"\n❌ Setup failed: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)
