"""
Scheduler service for automated price checking
"""

import time
import random
from typing import Dict, Optional, Any
from configparser import ConfigParser
from datetime import datetime

from apscheduler.schedulers.blocking import BlockingScheduler
from apscheduler.schedulers.background import BackgroundScheduler

from db.models import Database
from services.notifier import NotificationService
from services.logger import setup_logger
from trackers import get_tracker


class PriceMonitor:
    """Service for monitoring product prices"""
    
    def __init__(self):
        """Initialize price monitor"""
        self.logger = setup_logger(self.__class__.__name__)
        self.config = ConfigParser()
        self.config.read('config.ini')
        self.notifier = NotificationService()
        self.scheduler = None
    
    def check_product(self, product_id: int) -> Optional[float]:
        """
        Check price for a single product
        
        Args:
            product_id: Product ID to check
            
        Returns:
            New price if successful, None otherwise
        """
        db = Database()
        
        try:
            product = db.get_product(product_id)
            if not product:
                self.logger.error(f"Product {product_id} not found")
                return None
            
            # Get appropriate tracker
            tracker = get_tracker(product.url)
            if not tracker:
                self.logger.error(f"No tracker available for {product.url}")
                return None
            
            # Fetch current price
            self.logger.info(f"Checking price for: {product.name}")
            product_info = tracker.scrape(product.url)
            
            if not product_info or not product_info.get('price'):
                self.logger.warning(f"Failed to get price for: {product.name}")
                return None
            
            new_price = product_info['price']
            old_price = product.current_price
            
            # Update database
            db.update_product_price(product_id, new_price)
            db.add_price_history(
                product_id,
                new_price,
                availability=product_info.get('availability'),
                seller=product_info.get('seller')
            )
            
            self.logger.info(f"Price for {product.name}: ${new_price:.2f}")
            
            # Check if price dropped below target
            if new_price <= product.target_price:
                self.logger.info(f"PRICE ALERT: {product.name} - ${new_price:.2f} (target: ${product.target_price:.2f})")
                
                # Check notification cooldown
                cooldown_hours = self.config.getint('notifications', 'cooldown_hours', fallback=24)
                if db.should_send_notification(product_id, cooldown_hours):
                    # Send notifications
                    if old_price:
                        self.notifier.notify_price_drop(product, old_price, new_price, db)
                    else:
                        # First time checking, use target as reference
                        self.notifier.notify_price_drop(product, product.target_price, new_price, db)
            
            return new_price
            
        except Exception as e:
            self.logger.error(f"Error checking product {product_id}: {e}")
            return None
        
        finally:
            db.close()
    
    def check_all_products(self) -> Dict[str, int]:
        """
        Check prices for all products
        
        Returns:
            Dictionary with check statistics
        """
        db = Database()
        results = {
            'checked': 0,
            'updated': 0,
            'alerts': 0,
            'errors': 0
        }
        
        try:
            # Get products that need checking
            check_interval = self.config.getint('scheduler', 'check_interval_minutes', fallback=60)
            products = db.get_products_to_check(check_interval)
            
            if not products:
                self.logger.info("No products need checking")
                return results
            
            self.logger.info(f"Checking {len(products)} products...")
            
            for product in products:
                # Add random jitter to avoid patterns
                jitter = self.config.getint('scheduler', 'jitter_seconds', fallback=300)
                if jitter > 0:
                    delay = random.uniform(0, jitter)
                    time.sleep(delay)
                
                # Check product
                new_price = self.check_product(product.id)
                results['checked'] += 1
                
                if new_price:
                    results['updated'] += 1
                    
                    # Count alerts
                    if new_price <= product.target_price:
                        results['alerts'] += 1
                else:
                    results['errors'] += 1
                
                # Rate limiting between requests
                request_delay = self.config.getfloat('scheduler', 'request_delay', fallback=2)
                time.sleep(request_delay)
            
            self.logger.info(f"Check complete: {results}")
            
        except Exception as e:
            self.logger.error(f"Error in check_all_products: {e}")
            results['errors'] += 1
        
        finally:
            db.close()
        
        return results
    
    def start(self, interval_minutes: Optional[int] = None, background: bool = False):
        """
        Start the scheduler for continuous monitoring
        
        Args:
            interval_minutes: Check interval in minutes (overrides config)
            background: Run scheduler in background thread
        """
        if not interval_minutes:
            interval_minutes = self.config.getint('scheduler', 'check_interval_minutes', fallback=60)
        
        self.logger.info(f"Starting scheduler with {interval_minutes} minute interval")
        
        # Choose scheduler type
        if background:
            self.scheduler = BackgroundScheduler()
        else:
            self.scheduler = BlockingScheduler()
        
        # Schedule the job
        self.scheduler.add_job(
            self.check_all_products,
            'interval',
            minutes=interval_minutes,
            id='price_check',
            next_run_time=datetime.now()  # Run immediately on start
        )
        
        try:
            self.scheduler.start()
            
            if not background:
                # Keep running (blocking)
                while True:
                    time.sleep(1)
                    
        except KeyboardInterrupt:
            self.logger.info("Scheduler stopped by user")
            self.stop()
        except Exception as e:
            self.logger.error(f"Scheduler error: {e}")
            self.stop()
    
    def stop(self):
        """Stop the scheduler"""
        if self.scheduler and self.scheduler.running:
            self.scheduler.shutdown()
            self.logger.info("Scheduler stopped")
