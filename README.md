# 📊 Personal Price Tracker

A Python-based tool to monitor product prices from major e-commerce websites and get notified when prices drop below your target threshold.

## ✨ Features

- 🛒 **Multi-Platform Support**: Track prices from Amazon, eBay, Walmart, and other e-commerce sites
- 📈 **Price History**: Store and view historical price data in SQLite database
- 🔔 **Smart Notifications**: Get alerts via email or console when prices drop
- ⏰ **Automated Checking**: Schedule periodic price checks (daily or custom intervals)
- 💾 **Local Storage**: All data stored locally in SQLite database
- 🎯 **Target Price Alerts**: Set custom price thresholds for each product

## 📋 Prerequisites

- Python 3.7 or higher
- pip (Python package manager)
- Internet connection for price checking

## 🚀 Quick Start Guide

### Step 1: Clone or Download the Project

```bash
# Create a new directory for the project
mkdir price-tracker
cd price-tracker

# Download the files or create them manually
```

### Step 2: Install Required Libraries

```bash
pip install -r requirements.txt
```

Or install packages individually:

```bash
pip install requests beautifulsoup4 schedule lxml
```

### Step 3: Basic Usage - Command Line

#### Add a Product to Track

```bash
python price_tracker.py add --url "https://www.amazon.com/dp/PRODUCT_ID" --name "Product Name" --target 50.00
```

#### Check Prices

```bash
# Check all products
python price_tracker.py check

# Check specific product
python price_tracker.py check --id 1
```

#### List All Tracked Products

```bash
python price_tracker.py list
```

#### View Price History

```bash
python price_tracker.py history --id 1
```

### Step 4: Run the Example Demo

```bash
python example_usage.py
```

This will demonstrate all features with sample products.

## 📧 Email Notifications Setup

### Step 1: Configure Email Settings

Edit `config.json`:

```json
{
    "email": {
        "enabled": true,
        "smtp_server": "smtp.gmail.com",
        "smtp_port": 587,
        "sender_email": "your_email@gmail.com",
        "sender_password": "your_app_password",
        "recipient_email": "recipient@gmail.com"
    },
    "check_interval_hours": 24
}
```

### Step 2: Gmail Setup (Recommended)

1. Enable 2-Factor Authentication in your Google Account
2. Generate an App Password:
   - Go to Google Account Settings
   - Security → 2-Step Verification → App passwords
   - Generate a password for "Mail"
   - Use this password in `config.json`

### Step 3: Other Email Providers

- **Outlook**: smtp-mail.outlook.com (port 587)
- **Yahoo**: smtp.mail.yahoo.com (port 587)
- **Custom**: Use your email provider's SMTP settings

## ⏰ Automated Price Checking

### Run the Scheduler

```bash
python scheduler.py
```

This will:
- Check all products immediately
- Continue checking at the interval specified in `config.json`
- Log all activities to `price_tracker.log`
- Run until you stop it with Ctrl+C

### Run as Background Service (Linux/Mac)

```bash
nohup python scheduler.py &
```

### Run as Background Service (Windows)

Use Task Scheduler to run `scheduler.py` at startup.

## 🔧 Advanced Usage

### Python Script Integration

```python
from price_tracker import PriceTracker

# Initialize tracker
tracker = PriceTracker()

# Add a product
product_id = tracker.add_product(
    url="https://www.amazon.com/dp/B08N5WRWNW",
    name="Echo Dot",
    target_price=25.00
)

# Check price
current_price = tracker.check_price(product_id)
print(f"Current price: ${current_price}")

# Get price history
history = tracker.get_price_history(product_id)
for record in history:
    print(f"{record['timestamp']}: ${record['price']}")
```

## 📁 Project Structure

```
price-tracker/
│
├── price_tracker.py      # Main tracker module
├── scheduler.py          # Automated scheduling script
├── example_usage.py      # Demo script with examples
├── config.json          # Configuration file
├── requirements.txt     # Python dependencies
├── README.md           # This file
├── price_history.db    # SQLite database (created automatically)
└── price_tracker.log   # Log file (created when scheduler runs)
```

## 🗄️ Database Schema

### Products Table
- `id`: Unique identifier
- `url`: Product URL
- `name`: Product name
- `target_price`: Target price for notifications
- `last_checked`: Last check timestamp
- `created_at`: Creation timestamp

### Price History Table
- `id`: Unique identifier
- `product_id`: Foreign key to products
- `price`: Recorded price
- `currency`: Currency code
- `timestamp`: Recording timestamp

## 🛠️ Troubleshooting

### Common Issues and Solutions

#### 1. Price Extraction Fails

**Problem**: "Could not extract price from website"

**Solutions**:
- The website may have anti-bot protection
- Try adding a delay between requests
- Update the User-Agent in headers
- Check if the website requires login

#### 2. Email Notifications Not Working

**Problem**: "Failed to send email"

**Solutions**:
- Verify SMTP settings in `config.json`
- Check app password (not regular password)
- Ensure "Less secure app access" is enabled (if applicable)
- Check firewall/antivirus settings

#### 3. Database Errors

**Problem**: "Database is locked" or similar

**Solutions**:
- Ensure only one instance of scheduler is running
- Delete `price_history.db` to start fresh
- Check file permissions

#### 4. Import Errors

**Problem**: "ModuleNotFoundError"

**Solutions**:
```bash
pip install --upgrade pip
pip install -r requirements.txt
```

## 🎯 Best Practices

1. **Respect Rate Limits**: Don't check prices too frequently (minimum 1 hour intervals recommended)
2. **Use Realistic Headers**: The tool includes browser-like headers to avoid detection
3. **Monitor Responsibly**: Only track products you intend to purchase
4. **Keep URLs Updated**: Product URLs may change; update them if tracking fails
5. **Regular Backups**: Backup your `price_history.db` file periodically

## ⚠️ Important Notes

### Supported Websites

The tracker works best with:
- ✅ Amazon (most product pages)
- ✅ eBay (listing pages)
- ✅ Walmart (product pages)
- ⚠️ Other sites (may require customization)

### Limitations

- Some websites require login for prices
- Dynamic pricing may cause frequent fluctuations
- Anti-bot measures may block requests
- JavaScript-rendered prices may not be captured

### Legal Considerations

- Use this tool responsibly and in accordance with website terms of service
- This tool is for personal use only
- Respect robots.txt files and rate limits
- Do not use for commercial purposes without permission

## 📝 Customization

### Adding New Website Support

Edit `get_price_from_page()` in `price_tracker.py`:

```python
# Add new selectors for your website
elif 'yoursite' in domain:
    price_selectors = [
        'your-price-selector',
        'alternative-selector'
    ]
    # ... extraction logic
```

### Changing Notification Methods

Modify `send_notification()` to add:
- Desktop notifications (using `plyer`)
- SMS alerts (using Twilio)
- Discord/Slack webhooks
- Push notifications

## 🤝 Contributing

Feel free to customize and extend this tool for your needs. Some ideas:
- Add more e-commerce platforms
- Implement price prediction
- Create a web interface
- Add data visualization
- Export to CSV/Excel

## 📄 License

This tool is provided as-is for educational and personal use.

## 🆘 Support

For issues or questions:
1. Check the Troubleshooting section
2. Review the example scripts
3. Ensure all dependencies are installed
4. Check the log file for errors

---

**Happy Price Tracking! 🛍️💰**