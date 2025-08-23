# Personal Price Tracker

A production-ready Python 3.11 command-line tool for tracking product prices across major e-commerce sites and receiving alerts when prices drop below your target.

## Features

- **Multi-Site Support**: Track products from Amazon, eBay, and Walmart
- **Smart Notifications**: Email alerts via Gmail and optional desktop notifications
- **Price History**: SQLite database tracks all price changes over time
- **Flexible Scheduling**: Run once or continuously with configurable intervals
- **Resilient Scraping**: Multiple selector strategies, retry logic, and rate limiting
- **CLI Interface**: Easy-to-use commands for managing products and monitoring

## Quick Start

```bash
# Clone the repository
git clone https://github.com/yourusername/price-tracker.git
cd price-tracker

# Create virtual environment
python3.11 -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate

# Install dependencies
pip install -r requirements.txt

# Configure settings
cp config.example.ini config.ini
# Edit config.ini with your Gmail app password

# Initialize database
python price_tracker.py init-db

# Add your first product
python price_tracker.py add "https://www.amazon.com/dp/B08N5WRWNW" --target 40.00 --name "Echo Dot"

# Start monitoring
python price_tracker.py monitor
```

## Setup & Configuration

### Gmail Setup for Notifications

1. Enable 2-factor authentication on your Google account
2. Generate an app password:
   - Go to https://myaccount.google.com/apppasswords
   - Select "Mail" and generate password
3. Add credentials to `config.ini`:
   ```ini
   [email]
   sender_email = your.email@gmail.com
   sender_password = your-app-password-here
   recipient_email = your.email@gmail.com
   ```

### Configuration Options

Edit `config.ini` to customize:

- **Scheduler**: Check interval, jitter, rate limiting
- **Scraping**: User agent, timeouts, retry settings
- **Logging**: Log levels, file rotation
- **Notifications**: Cooldown periods, desktop alerts

## Usage Examples

### Adding Products

```bash
# Amazon product
python price_tracker.py add "https://www.amazon.com/dp/B08N5WRWNW" --target 35.00 --name "Echo Dot 4th Gen"

# eBay auction
python price_tracker.py add "https://www.ebay.com/itm/234567890123" --target 150.00 --name "Vintage Camera"

# Walmart item
python price_tracker.py add "https://www.walmart.com/ip/12345678" --target 25.00 --name "Bluetooth Speaker"
```

### Managing Products

```bash
# List all tracked products
python price_tracker.py list

# Update target price
python price_tracker.py set-target 1 45.00

# View price history
python price_tracker.py history 1 --days 30

# Remove product
python price_tracker.py remove 1
```

### Monitoring

```bash
# Run continuous monitoring (default interval from config)
python price_tracker.py monitor

# Run with custom interval (30 minutes)
python price_tracker.py monitor --interval 30

# Run single check
python price_tracker.py monitor --once

# Check specific product
python price_tracker.py check 1
```

## Example Session

```
$ python price_tracker.py add "https://www.amazon.com/dp/B0B4N77B65" --target 80.00 --name "AirPods Pro 2"
Fetching product details...
✓ Added: AirPods Pro 2
  Current Price: $249.00
  Target Price: $80.00
  Product ID: 1

$ python price_tracker.py list
╭─────────────────────── Tracked Products ────────────────────────╮
│ ID │ Name           │ Site   │ Current  │ Target  │ Status      │
├────┼────────────────┼────────┼──────────┼─────────┼─────────────┤
│ 1  │ AirPods Pro 2  │ Amazon │ $249.00  │ $80.00  │ +$169.00    │
╰──────────────────────────────────────────────────────────────────╯

$ python price_tracker.py monitor --once
Checking all products...
✓ Checked 1 products
```

## Sample Alert Email

When a product drops below your target price, you'll receive an email like:

```
Subject: 🎯 Price Drop Alert: Echo Dot 4th Gen

PRICE DROP ALERT!

Product: Echo Dot (4th Gen)
Site: Amazon

Old Price: $49.99
NEW PRICE: $34.99
Target Price: $40.00

YOU SAVE: $15.00 (30.0% OFF!)

View Product: https://www.amazon.com/dp/B08N5WRWNW
```

## Testing

Run the test suite:

```bash
# Run all tests
pytest

# Run with coverage
pytest --cov=. --cov-report=html

# Run specific test file
pytest tests/test_parsing.py -v
```

## Extensibility

### Adding a New Site

1. Create new tracker in `trackers/newsite.py`:
   ```python
   from trackers.base import TrackerBase
   
   class NewSiteTracker(TrackerBase):
       @property
       def site_name(self):
           return "NewSite"
       
       def extract_product_info(self, soup, url):
           # Implement extraction logic
           pass
   ```

2. Register in `trackers/__init__.py`:
   ```python
   if 'newsite' in domain:
       return NewSiteTracker()
   ```

### Desktop Notifications

Install optional dependency:
```bash
pip install plyer
```

Enable in `config.ini`:
```ini
[notifications]
desktop_enabled = true
```

### CSV Export

Add to your workflow:
```python
import csv
from db.models import Database

db = Database()
products = db.get_all_products()

with open('products.csv', 'w', newline='') as f:
    writer = csv.DictWriter(f, fieldnames=['name', 'current_price', 'target_price'])
    writer.writeheader()
    for p in products:
        writer.writerow({
            'name': p.name,
            'current_price': p.current_price,
            'target_price': p.target_price
        })
```

## Security & Compliance

### Best Practices

- **Respect Terms of Service**: Review each site's ToS before scraping
- **Rate Limiting**: Default 2-second delay between requests
- **User Agent**: Identifies as standard browser, not bot
- **Conservative Intervals**: Default 60-minute check interval
- **Secure Storage**: Credentials stored in config file, not code

### Compliance Notes

- HTML structures change frequently; selectors may need updates
- Some sites may block automated requests
- Use responsibly for personal use only
- Consider site-specific APIs when available

## Troubleshooting

### Common Issues

**CAPTCHA/Blocked Requests**
- Increase request delays in config
- Rotate user agents
- Use longer check intervals

**Changed Selectors**
- Check logs for extraction failures
- Update selectors in tracker classes
- Test with single product first

**SMTP Authentication Failed**
- Verify app password (not regular password)
- Check 2FA is enabled
- Confirm SMTP settings

**No Price Found**
- Product may be out of stock
- Page structure may have changed
- Try alternate product URL format

### Debug Mode

Enable detailed logging:
```ini
[logging]
level = DEBUG
```

Check logs:
```bash
tail -f price_tracker.log
tail -f errors.log
```

## Requirements

- Python 3.11+
- SQLite (included with Python)
- Internet connection
- Gmail account (for email alerts)

## License

MIT License - See LICENSE file for details

## Contributing

Pull requests welcome! Please include tests for new features.

## Support

For issues and questions, please use the GitHub issue tracker.