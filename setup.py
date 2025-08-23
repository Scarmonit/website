#!/usr/bin/env python3
"""
Setup Script for Price Tracker
Helps users get started quickly with installation and configuration.
"""

import os
import sys
import subprocess
import json

def print_banner():
    """Print welcome banner."""
    print("""
    ╔══════════════════════════════════════════════╗
    ║      Personal Price Tracker Setup            ║
    ╚══════════════════════════════════════════════╝
    """)

def check_python_version():
    """Check if Python version is 3.7+."""
    version = sys.version_info
    if version.major < 3 or (version.major == 3 and version.minor < 7):
        print("❌ Python 3.7+ is required")
        print(f"   Current version: {sys.version}")
        return False
    print(f"✅ Python {version.major}.{version.minor} detected")
    return True

def install_requirements():
    """Install required packages."""
    print("\n📦 Installing required packages...")
    try:
        subprocess.check_call([sys.executable, "-m", "pip", "install", "-r", "requirements.txt"])
        print("✅ All packages installed successfully")
        return True
    except subprocess.CalledProcessError:
        print("❌ Failed to install packages")
        print("   Try running: pip install -r requirements.txt")
        return False

def setup_config():
    """Set up configuration file."""
    print("\n⚙️ Setting up configuration...")
    
    if os.path.exists("config.json"):
        print("   Config file already exists")
        response = input("   Do you want to configure email notifications? (y/n): ").lower()
        if response != 'y':
            return True
    
    config = {
        "email": {
            "enabled": False,
            "smtp_server": "smtp.gmail.com",
            "smtp_port": 587,
            "sender_email": "",
            "sender_password": "",
            "recipient_email": ""
        },
        "check_interval_hours": 24,
        "notification_settings": {
            "console_alerts": True,
            "email_alerts": False
        }
    }
    
    print("\n📧 Email Configuration (optional, press Enter to skip)")
    
    email_setup = input("Do you want to set up email notifications? (y/n): ").lower()
    
    if email_setup == 'y':
        print("\nFor Gmail users:")
        print("1. Enable 2-Factor Authentication")
        print("2. Generate an App Password at: https://myaccount.google.com/apppasswords")
        print("3. Use the App Password (not your regular password)\n")
        
        config["email"]["sender_email"] = input("Sender email address: ").strip()
        config["email"]["sender_password"] = input("App password: ").strip()
        config["email"]["recipient_email"] = input("Recipient email (can be same as sender): ").strip()
        
        provider = input("Email provider (gmail/outlook/yahoo/other): ").lower()
        if provider == "outlook":
            config["email"]["smtp_server"] = "smtp-mail.outlook.com"
        elif provider == "yahoo":
            config["email"]["smtp_server"] = "smtp.mail.yahoo.com"
        elif provider == "other":
            config["email"]["smtp_server"] = input("SMTP server address: ").strip()
            config["email"]["smtp_port"] = int(input("SMTP port (usually 587): ") or "587")
        
        if config["email"]["sender_email"] and config["email"]["sender_password"]:
            config["email"]["enabled"] = True
            config["notification_settings"]["email_alerts"] = True
            print("✅ Email notifications configured")
        else:
            print("⚠️ Email configuration incomplete - notifications disabled")
    
    # Check interval
    interval = input("\nHow often to check prices (hours, default=24): ").strip()
    if interval.isdigit():
        config["check_interval_hours"] = int(interval)
    
    # Save configuration
    with open("config.json", "w") as f:
        json.dump(config, f, indent=4)
    
    print("✅ Configuration saved to config.json")
    return True

def test_installation():
    """Test the installation."""
    print("\n🧪 Testing installation...")
    try:
        import requests
        import bs4
        import schedule
        print("✅ All modules imported successfully")
        
        # Test database creation
        from price_tracker import PriceTracker
        tracker = PriceTracker()
        print("✅ Database initialized successfully")
        
        return True
    except ImportError as e:
        print(f"❌ Import error: {e}")
        return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def print_next_steps():
    """Print next steps for the user."""
    print("\n" + "="*50)
    print("🎉 Setup Complete!")
    print("="*50)
    print("\n📌 Next Steps:\n")
    print("1. Run the example demo:")
    print("   python example_usage.py\n")
    print("2. Add your first product:")
    print("   python price_tracker.py add --url \"PRODUCT_URL\" --name \"Product Name\" --target 99.99\n")
    print("3. Check prices manually:")
    print("   python price_tracker.py check\n")
    print("4. Start automated checking:")
    print("   python scheduler.py\n")
    print("5. View all commands:")
    print("   python price_tracker.py --help\n")
    print("📚 Full documentation: README.md")
    print("="*50)

def main():
    """Main setup function."""
    print_banner()
    
    # Check Python version
    if not check_python_version():
        sys.exit(1)
    
    # Install requirements
    if not install_requirements():
        print("\n⚠️ Please install packages manually and run setup again")
        sys.exit(1)
    
    # Setup configuration
    setup_config()
    
    # Test installation
    if test_installation():
        print_next_steps()
    else:
        print("\n⚠️ Some issues were detected. Please check the errors above.")
        print("   You may need to install packages manually:")
        print("   pip install requests beautifulsoup4 schedule")

if __name__ == "__main__":
    main()