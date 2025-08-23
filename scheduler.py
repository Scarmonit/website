#!/usr/bin/env python3
"""
Price Tracker Scheduler
Runs the price tracker at regular intervals to check for price changes.
"""

import schedule
import time
import json
import logging
from datetime import datetime
from price_tracker import PriceTracker

# Set up logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('price_tracker.log'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)


def run_price_check():
    """Run price check for all products."""
    logger.info("Starting scheduled price check...")
    
    try:
        tracker = PriceTracker()
        tracker.check_all_products()
        logger.info("Scheduled price check completed successfully")
    except Exception as e:
        logger.error(f"Error during scheduled price check: {e}")


def load_schedule_config():
    """Load scheduling configuration."""
    try:
        with open('config.json', 'r') as f:
            config = json.load(f)
            return config.get('check_interval_hours', 24)
    except FileNotFoundError:
        logger.warning("Config file not found. Using default interval of 24 hours.")
        return 24


def main():
    """Main scheduler function."""
    print("""
    ╔══════════════════════════════════════════════╗
    ║     Price Tracker Scheduler - Running        ║
    ╚══════════════════════════════════════════════╝
    """)
    
    # Load configuration
    interval_hours = load_schedule_config()
    
    logger.info(f"Scheduler started. Will check prices every {interval_hours} hours")
    print(f"✓ Checking prices every {interval_hours} hours")
    print("✓ Press Ctrl+C to stop\n")
    
    # Run initial check
    run_price_check()
    
    # Schedule regular checks
    schedule.every(interval_hours).hours.do(run_price_check)
    
    # Keep the scheduler running
    try:
        while True:
            schedule.run_pending()
            time.sleep(60)  # Check every minute for pending jobs
    except KeyboardInterrupt:
        logger.info("Scheduler stopped by user")
        print("\n✓ Scheduler stopped")


if __name__ == "__main__":
    main()