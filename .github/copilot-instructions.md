# Personal Price Tracker

Personal Price Tracker is a Python-based tool that monitors product prices from e-commerce websites (Amazon, eBay, Walmart) and sends notifications when prices drop below target thresholds. It uses SQLite for local storage, supports email and desktop notifications, and includes automated scheduling capabilities.

Always reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.

## Working Effectively

### Bootstrap, Build, and Test the Repository
- Install Python dependencies: `pip install -r requirements.txt` -- takes 5 seconds. NEVER CANCEL.
- Run setup script: `python setup.py` -- takes 6 seconds. NEVER CANCEL. Set timeout to 30+ seconds.
- Run system tests: `python test_system.py` -- takes 0.2 seconds. NEVER CANCEL. Set timeout to 30+ seconds.
- Run quick start: `python quick_start.py` -- takes 10 seconds (interactive). NEVER CANCEL. Set timeout to 60+ seconds.

### Run the Application
- ALWAYS run the bootstrapping steps first before using the application.
- Basic CLI usage: `python price_tracker.py --help`
- Add product: `python price_tracker.py add --url "PRODUCT_URL" --name "Product Name" --target 50.00` -- takes 0.3 seconds
- Check prices: `python price_tracker.py check` -- takes 2-3 seconds per product. NEVER CANCEL. Set timeout to 60+ seconds.
- List products: `python price_tracker.py list` -- takes 0.1 seconds
- View history: `python price_tracker.py history --id 1` -- takes 0.1 seconds

### Run Examples and Demos
- ALWAYS run the bootstrapping steps first.
- Full demo suite: `python examples.py` -- takes 15 seconds. NEVER CANCEL. Set timeout to 60+ seconds.
- Example usage: `python example_usage.py` -- takes 3 seconds. NEVER CANCEL. Set timeout to 30+ seconds.

## Validation

### Required Testing Steps
- ALWAYS run `python test_system.py` after making changes to core functionality.
- ALWAYS test basic functionality with `python price_tracker.py list` to verify database connectivity.
- NEVER CANCEL test commands - they complete quickly (under 1 second typically).

### Manual Validation Requirements
- ALWAYS test end-to-end workflows after making changes:
  1. Add a test product: `python price_tracker.py add --url "https://example.com/test" --name "Test" --target 50.00`
  2. List products to verify: `python price_tracker.py list`
  3. Check prices (will fail for example.com but should not crash): `python price_tracker.py check`
  4. Verify no Python exceptions occur during basic operations

### Known Test Limitations
- Network requests to external sites will fail in sandboxed environment - this is expected behavior
- Desktop notifications require GUI environment and may fail with permission errors - this is expected
- Email notifications require valid SMTP configuration - disabled by default
- Test system may show 4/6 tests passing - this is the baseline expectation

## Common Tasks

### Key Project Files and Locations
The following files are essential for development:

#### Core Application Files
- `price_tracker.py` - Main application class and CLI interface
- `database.py` - SQLite database operations and schema
- `scraper.py` - Web scraping logic for e-commerce sites
- `notifications.py` - Email and desktop notification handling
- `scheduler.py` - Automated price checking daemon

#### Setup and Testing Files
- `setup.py` - Installation and initial configuration script
- `quick_start.py` - Interactive setup for new users
- `test_system.py` - Comprehensive system test suite
- `examples.py` - Demo suite showcasing all features
- `example_usage.py` - Basic usage demonstration

#### Configuration Files
- `requirements.txt` - Python dependencies
- `config.example.json` - Example configuration with all options
- `config.json` - User configuration (created during setup)

#### Project Structure Output
```
ls -la [repo-root]
total 352
drwxr-xr-x 9 runner docker  4096 Sep  7 19:55 .
drwxr-xr-x 3 runner docker  4096 Sep  7 19:54 ..
drwxr-xr-x 7 runner docker  4096 Sep  7 19:55 .git
-rw-r--r-- 1 runner docker 18181 Sep  7 19:55 README.md
drwxr-xr-x 2 runner docker  4096 Sep  7 19:55 __pycache__
-rw-r--r-- 1 runner docker   824 Sep  7 19:55 config.example.ini
-rw-r--r-- 1 runner docker  1236 Sep  7 19:55 config.example.json
-rw-r--r-- 1 runner docker   740 Sep  7 19:55 config.json
-rw-r--r-- 1 runner docker  9901 Sep  7 19:55 database.py
drwxr-xr-x 2 runner docker  4096 Sep  7 19:55 db
-rw-r--r-- 1 runner docker  7018 Sep  7 19:55 example_usage.py
-rw-r--r-- 1 runner docker  8755 Sep  7 19:55 examples.py
-rw-r--r-- 1 runner docker  6139 Sep  7 19:55 notifications.py
-rw-r--r-- 1 runner docker 16384 Sep  7 19:55 price_tracker.db
-rw-r--r-- 1 runner docker 15460 Sep  7 19:55 price_tracker.py
-rw-r--r-- 1 runner docker  8128 Sep  7 19:55 quick_start.py
-rw-r--r-- 1 runner docker   239 Sep  7 19:55 requirements.txt
-rw-r--r-- 1 runner docker  3594 Sep  7 19:55 scheduler.py
-rw-r--r-- 1 runner docker 11574 Sep  7 19:55 scraper.py
drwxr-xr-x 2 runner docker  4096 Sep  7 19:55 services
-rw-r--r-- 1 runner docker  5297 Sep  7 19:55 setup.py
-rw-r--r-- 1 runner docker 12288 Sep  7 19:55 test_price_tracker.db
-rw-r--r-- 1 runner docker 12288 Sep  7 19:55 test_system.db
-rw-r--r-- 1 runner docker 10981 Sep  7 19:55 test_system.py
drwxr-xr-x 3 runner docker  4096 Sep  7 19:55 tests
drwxr-xr-x 2 runner docker  4096 Sep  7 19:55 trackers
drwxr-xr-x 2 runner docker  4096 Sep  7 19:55 utils
```

#### Key Dependencies from requirements.txt
```
cat requirements.txt
# Price Tracker Requirements
# Python 3.7+ required

# Web scraping
requests==2.31.0
beautifulsoup4==4.12.2

# Scheduling
schedule==1.2.0

# Optional: For better HTML parsing
lxml==4.9.3

# Optional: For desktop notifications (cross-platform)
plyer==2.1
```

### When Working with Specific Components

#### Database Changes
- ALWAYS run `python test_system.py` to verify database functionality
- Database files are auto-generated (.db files) - do not modify manually
- Check database integrity with: `python price_tracker.py list`

#### Scraper Changes
- Test with: `python test_system.py` (includes scraper validation)
- Web scraping will fail in sandboxed environment - this is expected
- Focus on selector logic and error handling rather than actual web requests

#### Notification Changes
- Desktop notifications require GUI and may fail in sandboxed environment
- Email notifications require SMTP configuration
- Test notification templates and formatting logic instead of actual delivery

#### Configuration Changes
- Always validate config loading with: `python test_system.py`
- Use `config.example.json` as the reference for all available options
- Test configuration loading edge cases (missing files, invalid JSON)

## Important Notes

### Timing Expectations and Timeouts
- **Dependency installation**: 5 seconds (set timeout to 30+ seconds)
- **Setup script**: 6 seconds (set timeout to 60+ seconds) 
- **System tests**: 0.2 seconds (set timeout to 30+ seconds)
- **Price checking**: 2-3 seconds per product (set timeout to 60+ seconds)
- **Example demos**: 3-15 seconds (set timeout to 60+ seconds)

### Critical Reminders
- **NEVER CANCEL** any build, test, or example command - they complete quickly
- Always set appropriate timeouts (30-60 seconds) even for fast operations
- Network operations will fail in sandboxed environment - this is expected behavior
- Database and file operations work normally and should be tested thoroughly

### No CI/CD Pipeline
- This repository has no .github/workflows directory
- No automated CI builds to consider
- Manual testing is the primary validation method

### Known Issues and Workarounds
- Desktop notifications fail with "Permission denied" in sandboxed environment - expected
- Network requests to e-commerce sites fail due to network restrictions - expected  
- Some system tests may fail due to environment limitations - 4/6 passing is baseline
- Import error for MIMEText was fixed - notifications.py line 12 corrected

### Development Best Practices
- Always test changes with the system test suite first
- Use example URLs for testing (they will fail gracefully)
- Focus on logic and error handling rather than actual external API calls
- Validate configuration loading and database operations thoroughly
- Check that CLI commands don't crash even when network operations fail