#!/usr/bin/env python3
"""
Setup script for Personal Price Tracker

This script helps with initial setup and makes files executable.
"""

import os
import sys
import stat
from pathlib import Path


def make_executable(file_path):
    """Make a file executable on Unix-like systems"""
    try:
        current_permissions = os.stat(file_path).st_mode
        os.chmod(file_path, current_permissions | stat.S_IEXEC)
        return True
    except Exception as e:
        print(f"Warning: Could not make {file_path} executable: {e}")
        return False


def setup_permissions():
    """Setup file permissions for the project"""
    print("🔧 Setting up file permissions...")
    
    executable_files = [
        'price_tracker.py',
        'examples.py',
        'quick_start.py',
        'test_system.py'
    ]
    
    for file_path in executable_files:
        if os.path.exists(file_path):
            if make_executable(file_path):
                print(f"   ✅ Made {file_path} executable")
            else:
                print(f"   ⚠️  Could not make {file_path} executable")
        else:
            print(f"   ❌ File not found: {file_path}")


def check_project_structure():
    """Check that all required files are present"""
    print("\n📁 Checking project structure...")
    
    required_files = [
        ('README.md', 'Main documentation'),
        ('requirements.txt', 'Python dependencies'),
        ('config.example.json', 'Example configuration'),
        ('price_tracker.py', 'Main application'),
        ('database.py', 'Database operations'),
        ('scraper.py', 'Web scraping'),
        ('notifications.py', 'Notification system'),
        ('examples.py', 'Usage examples'),
        ('quick_start.py', 'Quick setup script'),
        ('test_system.py', 'System tests')
    ]
    
    all_present = True
    
    for file_path, description in required_files:
        if os.path.exists(file_path):
            file_size = os.path.getsize(file_path)
            print(f"   ✅ {file_path:<20} ({file_size:,} bytes) - {description}")
        else:
            print(f"   ❌ {file_path:<20} MISSING - {description}")
            all_present = False
    
    return all_present


def show_quick_start():
    """Show quick start instructions"""
    print("\n" + "=" * 60)
    print("🚀 Personal Price Tracker - Setup Complete!")
    print("=" * 60)
    
    print("\n📚 Quick Start Options:")
    
    print("\n1. 🏃 Fastest Start (Recommended for beginners):")
    print("   python quick_start.py")
    
    print("\n2. 🧪 Test System First:")
    print("   python test_system.py")
    
    print("\n3. 📖 Manual Setup:")
    print("   Step 1: pip install -r requirements.txt")
    print("   Step 2: cp config.example.json config.json")
    print("   Step 3: python price_tracker.py --init")
    print("   Step 4: python price_tracker.py --add 'URL' --name 'Name' --threshold 50.00")
    
    print("\n4. 🎮 See Examples:")
    print("   python examples.py")
    
    print("\n📧 Email Setup (Optional):")
    print("   - Edit config.json with your Gmail credentials")
    print("   - Use Gmail App Password (not regular password)")
    print("   - Enable 2-factor authentication first")
    
    print("\n❓ Need Help?")
    print("   python price_tracker.py --help")
    print("   📘 Full guide: README.md")
    
    print("\n🎯 Common Commands:")
    print("   Add product:    python price_tracker.py --add URL --name 'Name' --threshold 50")
    print("   Check prices:   python price_tracker.py --check")
    print("   List products:  python price_tracker.py --list")
    print("   Auto monitor:   python price_tracker.py --daemon")


def main():
    """Main setup function"""
    print("🛠️  Personal Price Tracker - Setup")
    print("=" * 40)
    
    # Check Python version
    if sys.version_info < (3, 7):
        print(f"❌ Python 3.7+ required. Current: {sys.version}")
        return False
    
    print(f"✅ Python version: {sys.version.split()[0]}")
    
    # Setup permissions (Unix/Linux/Mac)
    if os.name != 'nt':  # Not Windows
        setup_permissions()
    else:
        print("🖥️  Windows detected - skipping executable permissions")
    
    # Check project structure
    if not check_project_structure():
        print("\n❌ Some files are missing. Please ensure all files are present.")
        return False
    
    # Show next steps
    show_quick_start()
    
    return True


if __name__ == "__main__":
    try:
        success = main()
        if not success:
            sys.exit(1)
    except KeyboardInterrupt:
        print("\n👋 Setup interrupted")
        sys.exit(1)
    except Exception as e:
        print(f"\n❌ Setup failed: {e}")
        sys.exit(1)
