# Personal Price Tracker

Personal Price Tracker is a Python-based tool that monitors product prices from e-commerce websites (Amazon, eBay, Walmart) and sends notifications when prices drop below target thresholds. It uses SQLite for local storage, supports email and desktop notifications, and includes automated scheduling capabilities.

Always reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.

## Working Effectively

### Bootstrap, Build, and Test the Repository
- Install Python dependencies: `pip install -r requirements.txt` -- takes 5 seconds. NEVER CANCEL. Set timeout to 60+ seconds.
- Run setup script: `python setup.py` -- takes 17 seconds (interactive). NEVER CANCEL. Set timeout to 60+ seconds.
- Run system tests: `python test_system.py` -- takes 0.2 seconds. NEVER CANCEL. Set timeout to 30+ seconds.
- Run quick start: `python quick_start.py` -- takes 38 seconds (interactive). NEVER CANCEL. Set timeout to 120+ seconds.

### Run the Application
- ALWAYS run the bootstrapping steps first before using the application.
- Basic CLI usage: `python price_tracker.py --help`
- Add product: `python price_tracker.py add --url "PRODUCT_URL" --name "Product Name" --target 50.00` -- takes 0.3 seconds
- Check prices: `python price_tracker.py check` -- takes 2-3 seconds per product. NEVER CANCEL. Set timeout to 60+ seconds.
- List products: `python price_tracker.py list` -- takes 0.1 seconds
- View history: `python price_tracker.py history --id 1` -- takes 0.1 seconds

### Run Examples and Demos
- ALWAYS run the bootstrapping steps first.
- Full demo suite: `python examples.py` -- takes 24 seconds. NEVER CANCEL. Set timeout to 120+ seconds.
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

### Interactive Command Handling
- Both `python setup.py` and `python quick_start.py` are interactive and require user input
- For automated testing, provide responses like `n{enter}` for setup and `y{enter}` for quick start
- Use write_bash tool with appropriate delays when running these commands interactively

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
total 308
drwxr-xr-x 10 runner runner  4096 Sep 11 00:03 .
drwxr-xr-x  3 runner runner  4096 Sep 11 00:03 ..
drwxrwxr-x  7 runner runner  4096 Sep 11 00:03 .git
drwxrwxr-x  2 runner runner  4096 Sep 11 00:03 .github
-rw-rw-r--  1 runner runner  2776 Sep 11 00:03 .gitignore
-rw-rw-r--  1 runner runner  7711 Sep 11 00:03 README.md
drwxrwxr-x  2 runner runner  4096 Sep 11 00:03 __pycache__
-rw-rw-r--  1 runner runner   784 Sep 11 00:03 config.demo.json
-rw-rw-r--  1 runner runner   964 Sep 11 00:03 config.example.ini
-rw-rw-r--  1 runner runner  1444 Sep 11 00:03 config.example.json
-rw-rw-r--  1 runner runner   394 Sep 11 00:03 config.json
-rw-rw-r--  1 runner runner 13649 Sep 11 00:03 database.py
drwxrwxr-x  2 runner runner  4096 Sep 11 00:03 db
-rw-rw-r--  1 runner runner  3842 Sep 11 00:03 example_usage.py
-rw-rw-r--  1 runner runner  9368 Sep 11 00:03 examples.py
-rw-rw-r--  1 runner runner 14146 Sep 11 00:03 notifications.py
-rw-rw-r--  1 runner runner 20480 Sep 11 00:03 price_history.db
-rw-rw-r--  1 runner runner 24576 Sep 11 00:03 price_tracker.db
-rw-rw-r--  1 runner runner 18939 Sep 11 00:03 price_tracker.py
-rw-rw-r--  1 runner runner 10347 Sep 11 00:03 quick_start.py
-rw-rw-r--  1 runner runner   253 Sep 11 00:03 requirements.txt
-rw-rw-r--  1 runner runner  2397 Sep 11 00:03 scheduler.py
-rw-rw-r--  1 runner runner 15363 Sep 11 00:03 scraper.py
drwxrwxr-x  2 runner runner  4096 Sep 11 00:03 services
-rw-rw-r--  1 runner runner  6221 Sep 11 00:03 setup.py
-rw-rw-r--  1 runner runner 20480 Sep 11 00:03 test_config.json
-rw-rw-r--  1 runner runner   224 Sep 11 00:03 test_integration_config.json
-rw-rw-r--  1 runner runner 24576 Sep 11 00:03 test_price_tracker.db
-rw-rw-r--  1 runner runner 24576 Sep 11 00:03 test_system.db
-rw-rw-r--  1 runner runner 11576 Sep 11 00:03 test_system.py
drwxrwxr-x  3 runner runner  4096 Sep 11 00:03 tests
drwxrwxr-x  2 runner runner  4096 Sep 11 00:03 trackers
drwxrwxr-x  2 runner runner  4096 Sep 11 00:03 utils
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
- **Dependency installation**: 5 seconds (set timeout to 60+ seconds)
- **Setup script**: 17 seconds (set timeout to 60+ seconds) 
- **System tests**: 0.2 seconds (set timeout to 30+ seconds)
- **Quick start (interactive)**: 38 seconds (set timeout to 120+ seconds)
- **Price checking**: 2-3 seconds per product (set timeout to 60+ seconds)
- **Example demos**: 3-24 seconds (set timeout to 120+ seconds)

### Critical Reminders
- **NEVER CANCEL** any build, test, or example command - they complete quickly
- Always set appropriate timeouts (60-120 seconds) for all operations, even fast ones
- Network operations will fail in sandboxed environment - this is expected behavior
- Database and file operations work normally and should be tested thoroughly

### No CI/CD Pipeline
- This repository has no .github/workflows directory
- No automated CI builds to consider
- Manual testing is the primary validation method

### Unit Tests and Additional Testing
- Unit tests exist in the `tests/` directory but require pytest which is not in requirements.txt
- Tests use pytest framework: `tests/test_models.py`, `tests/test_parsing.py`
- Primary testing method is the system test suite: `python test_system.py`
- Do not attempt to run pytest-based unit tests unless pytest is explicitly installed

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