"""
Database module for the Personal Price Tracker

This module handles all SQLite database operations including:
- Database initialization and schema creation
- Product management (add, remove, update)
- Price history tracking
- Notification logging
"""

import sqlite3
import os
from datetime import datetime
from typing import List, Dict, Optional, Tuple


class PriceTrackerDB:
    """Database manager for the price tracker application"""
    
    def __init__(self, db_path: str = "price_tracker.db"):
        """
        Initialize database connection
        
        Args:
            db_path: Path to SQLite database file
        """
        self.db_path = db_path
        self.init_database()
    
    def init_database(self):
        """Create database tables if they don't exist"""
        with sqlite3.connect(self.db_path) as conn:
            cursor = conn.cursor()
            
            # Products table - stores tracked products
            cursor.execute("""
                CREATE TABLE IF NOT EXISTS products (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    url TEXT UNIQUE NOT NULL,
                    name TEXT NOT NULL,
                    threshold REAL NOT NULL,
                    site TEXT NOT NULL,
                    current_price REAL,
                    last_checked TIMESTAMP,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    active BOOLEAN DEFAULT 1
                )
            """)
            
            # Price history table - tracks all price changes
            cursor.execute("""
                CREATE TABLE IF NOT EXISTS price_history (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    product_id INTEGER NOT NULL,
                    price REAL NOT NULL,
                    checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    status TEXT DEFAULT 'success',
                    error_message TEXT,
                    FOREIGN KEY (product_id) REFERENCES products (id)
                )
            """)
            
            # Notifications table - prevents spam notifications
            cursor.execute("""
                CREATE TABLE IF NOT EXISTS notifications (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    product_id INTEGER NOT NULL,
                    notification_type TEXT NOT NULL,
                    price REAL NOT NULL,
                    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (product_id) REFERENCES products (id)
                )
            """)
            
            conn.commit()
            print("✅ Database initialized successfully")
    
    def add_product(self, url: str, name: str, threshold: float, site: str) -> int:
        """
        Add a new product to track
        
        Args:
            url: Product URL
            name: Product name/description
            threshold: Price threshold for notifications
            site: E-commerce site name
            
        Returns:
            Product ID of the added product
            
        Raises:
            sqlite3.IntegrityError: If URL already exists
        """
        with sqlite3.connect(self.db_path) as conn:
            cursor = conn.cursor()
            
            try:
                cursor.execute("""
                    INSERT INTO products (url, name, threshold, site)
                    VALUES (?, ?, ?, ?)
                """, (url, name, threshold, site))
                
                product_id = cursor.lastrowid
                conn.commit()
                
                print(f"✅ Added product: {name} (ID: {product_id})")
                return product_id
                
            except sqlite3.IntegrityError:
                raise ValueError(f"Product URL already exists: {url}")
    
    def remove_product(self, product_id: int) -> bool:
        """
        Remove a product from tracking (soft delete)
        
        Args:
            product_id: ID of product to remove
            
        Returns:
            True if product was removed, False if not found
        """
        with sqlite3.connect(self.db_path) as conn:
            cursor = conn.cursor()
            
            cursor.execute("""
                UPDATE products SET active = 0 
                WHERE id = ? AND active = 1
            """, (product_id,))
            
            if cursor.rowcount > 0:
                conn.commit()
                print(f"✅ Removed product ID: {product_id}")
                return True
            else:
                print(f"❌ Product ID {product_id} not found")
                return False
    
    def get_active_products(self) -> List[Dict]:
        """
        Get all active products to track
        
        Returns:
            List of product dictionaries
        """
        with sqlite3.connect(self.db_path) as conn:
            conn.row_factory = sqlite3.Row  # Enable dict-like access
            cursor = conn.cursor()
            
            cursor.execute("""
                SELECT id, url, name, threshold, site, current_price, last_checked
                FROM products 
                WHERE active = 1
                ORDER BY created_at DESC
            """)
            
            return [dict(row) for row in cursor.fetchall()]
    
    def get_product_by_id(self, product_id: int) -> Optional[Dict]:
        """
        Get a specific product by ID
        
        Args:
            product_id: Product ID
            
        Returns:
            Product dictionary or None if not found
        """
        with sqlite3.connect(self.db_path) as conn:
            conn.row_factory = sqlite3.Row
            cursor = conn.cursor()
            
            cursor.execute("""
                SELECT id, url, name, threshold, site, current_price, last_checked, created_at
                FROM products 
                WHERE id = ? AND active = 1
            """, (product_id,))
            
            row = cursor.fetchone()
            return dict(row) if row else None
    
    def update_product_price(self, product_id: int, price: float, status: str = 'success', 
                           error_message: str = None) -> bool:
        """
        Update product's current price and add to history
        
        Args:
            product_id: Product ID
            price: New price (None if error occurred)
            status: Status of the price check ('success' or 'error')
            error_message: Error message if status is 'error'
            
        Returns:
            True if update was successful
        """
        with sqlite3.connect(self.db_path) as conn:
            cursor = conn.cursor()
            
            # Update current price and last checked time
            if status == 'success' and price is not None:
                cursor.execute("""
                    UPDATE products 
                    SET current_price = ?, last_checked = ?
                    WHERE id = ?
                """, (price, datetime.now(), product_id))
            else:
                cursor.execute("""
                    UPDATE products 
                    SET last_checked = ?
                    WHERE id = ?
                """, (datetime.now(), product_id))
            
            # Add to price history
            cursor.execute("""
                INSERT INTO price_history (product_id, price, status, error_message)
                VALUES (?, ?, ?, ?)
            """, (product_id, price, status, error_message))
            
            conn.commit()
            return cursor.rowcount > 0
    
    def get_price_history(self, product_id: int, limit: int = 50) -> List[Dict]:
        """
        Get price history for a product
        
        Args:
            product_id: Product ID
            limit: Maximum number of records to return
            
        Returns:
            List of price history records
        """
        with sqlite3.connect(self.db_path) as conn:
            conn.row_factory = sqlite3.Row
            cursor = conn.cursor()
            
            cursor.execute("""
                SELECT price, checked_at, status, error_message
                FROM price_history 
                WHERE product_id = ?
                ORDER BY checked_at DESC
                LIMIT ?
            """, (product_id, limit))
            
            return [dict(row) for row in cursor.fetchall()]
    
    def log_notification(self, product_id: int, notification_type: str, price: float):
        """
        Log a sent notification to prevent spam
        
        Args:
            product_id: Product ID
            notification_type: Type of notification ('email' or 'desktop')
            price: Price that triggered the notification
        """
        with sqlite3.connect(self.db_path) as conn:
            cursor = conn.cursor()
            
            cursor.execute("""
                INSERT INTO notifications (product_id, notification_type, price)
                VALUES (?, ?, ?)
            """, (product_id, notification_type, price))
            
            conn.commit()
    
    def should_send_notification(self, product_id: int, price: float, 
                                cooldown_hours: int = 24) -> bool:
        """
        Check if we should send a notification (avoid spam)
        
        Args:
            product_id: Product ID
            price: Current price
            cooldown_hours: Hours to wait between notifications for same product
            
        Returns:
            True if notification should be sent
        """
        with sqlite3.connect(self.db_path) as conn:
            cursor = conn.cursor()
            
            # Use parameterized query to avoid any potential issues with string formatting
            cursor.execute("""
                SELECT COUNT(*) 
                FROM notifications 
                WHERE product_id = ? 
                AND sent_at > datetime('now', '-' || ? || ' hours')
            """, (product_id, cooldown_hours))
            
            count = cursor.fetchone()[0]
            return count == 0
    
    def get_database_stats(self) -> Dict:
        """
        Get database statistics
        
        Returns:
            Dictionary with database statistics
        """
        with sqlite3.connect(self.db_path) as conn:
            cursor = conn.cursor()
            
            stats = {}
            
            # Count active products
            cursor.execute("SELECT COUNT(*) FROM products WHERE active = 1")
            stats['active_products'] = cursor.fetchone()[0]
            
            # Count total price checks
            cursor.execute("SELECT COUNT(*) FROM price_history")
            stats['total_price_checks'] = cursor.fetchone()[0]
            
            # Count successful price checks
            cursor.execute("SELECT COUNT(*) FROM price_history WHERE status = 'success'")
            stats['successful_checks'] = cursor.fetchone()[0]
            
            # Count notifications sent
            cursor.execute("SELECT COUNT(*) FROM notifications")
            stats['notifications_sent'] = cursor.fetchone()[0]
            
            # Database file size
            stats['db_size_mb'] = round(os.path.getsize(self.db_path) / 1024 / 1024, 2)
            
            return stats
    
    def cleanup_old_history(self, days_to_keep: int = 90):
        """
        Clean up old price history records to keep database size manageable
        
        Args:
            days_to_keep: Number of days of history to keep
        """
        with sqlite3.connect(self.db_path) as conn:
            cursor = conn.cursor()
            
            # Use parameterized queries for consistency and safety
            cursor.execute("""
                DELETE FROM price_history 
                WHERE checked_at < datetime('now', '-' || ? || ' days')
            """, (days_to_keep,))
            
            deleted_count = cursor.rowcount
            
            cursor.execute("""
                DELETE FROM notifications 
                WHERE sent_at < datetime('now', '-' || ? || ' days')
            """, (days_to_keep,))
            
            conn.commit()
            
            if deleted_count > 0:
                print(f"🧹 Cleaned up {deleted_count} old history records")


if __name__ == "__main__":
    # Test the database functionality
    print("🧪 Testing database functionality...")
    
    # Initialize database
    db = PriceTrackerDB("test_price_tracker.db")
    
    try:
        # Add a test product
        product_id = db.add_product(
            url="https://amazon.com/dp/TEST123",
            name="Test Product",
            threshold=25.00,
            site="Amazon"
        )
        
        # Update price
        db.update_product_price(product_id, 29.99)
        
        # Get products
        products = db.get_active_products()
        print(f"📦 Found {len(products)} products")
        
        # Get price history
        history = db.get_price_history(product_id)
        print(f"📊 Found {len(history)} price history records")
        
        # Get stats
        stats = db.get_database_stats()
        print(f"📈 Database stats: {stats}")
        
        print("✅ Database test completed successfully!")
        
    except Exception as e:
        print(f"❌ Database test failed: {e}")
    
    # Clean up test database
    if os.path.exists("test_price_tracker.db"):
        os.remove("test_price_tracker.db")
        print("🗑️ Test database cleaned up")
