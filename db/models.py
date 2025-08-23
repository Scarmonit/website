"""
Database models and operations for price tracker
"""

import sqlite3
from dataclasses import dataclass
from datetime import datetime, timedelta
from typing import List, Optional, Dict, Any
from pathlib import Path
from configparser import ConfigParser

from services.logger import setup_logger


@dataclass
class Product:
    """Product model"""
    url: str
    name: str
    site: str
    target_price: float
    current_price: Optional[float] = None
    id: Optional[int] = None
    lowest_price: Optional[float] = None
    highest_price: Optional[float] = None
    last_checked: Optional[datetime] = None
    created_at: Optional[datetime] = None
    updated_at: Optional[datetime] = None
    active: bool = True
    notification_sent_at: Optional[datetime] = None


@dataclass
class PriceHistory:
    """Price history model"""
    product_id: int
    price: float
    checked_at: datetime
    id: Optional[int] = None
    availability: Optional[str] = None
    seller: Optional[str] = None
    notes: Optional[str] = None


@dataclass
class Notification:
    """Notification model"""
    product_id: int
    type: str
    sent_at: datetime
    id: Optional[int] = None
    price_at_notification: Optional[float] = None
    message: Optional[str] = None
    success: bool = True


class Database:
    """Database operations manager"""
    
    def __init__(self, db_path: Optional[str] = None):
        """Initialize database connection"""
        self.logger = setup_logger(self.__class__.__name__)
        
        if not db_path:
            config = ConfigParser()
            config.read('config.ini')
            db_path = config.get('database', 'path', fallback='price_tracker.db')
        
        self.db_path = db_path
        self.conn = None
        self._connect()
    
    def _connect(self):
        """Create database connection"""
        self.conn = sqlite3.connect(self.db_path, check_same_thread=False)
        self.conn.row_factory = sqlite3.Row
        self.conn.execute("PRAGMA foreign_keys = ON")
    
    def init_database(self):
        """Initialize database with schema"""
        schema_path = Path(__file__).parent / 'schema.sql'
        
        with open(schema_path, 'r') as f:
            schema = f.read()
        
        self.conn.executescript(schema)
        self.conn.commit()
        self.logger.info("Database initialized successfully")
    
    def add_product(self, product: Product) -> int:
        """Add a new product to track"""
        cursor = self.conn.cursor()
        
        cursor.execute("""
            INSERT INTO products (url, name, site, target_price, current_price)
            VALUES (?, ?, ?, ?, ?)
        """, (product.url, product.name, product.site, product.target_price, product.current_price))
        
        self.conn.commit()
        product_id = cursor.lastrowid
        
        self.logger.info(f"Added product: {product.name} (ID: {product_id})")
        return product_id
    
    def get_product(self, product_id: int) -> Optional[Product]:
        """Get a product by ID"""
        cursor = self.conn.cursor()
        
        cursor.execute("SELECT * FROM products WHERE id = ? AND active = 1", (product_id,))
        row = cursor.fetchone()
        
        if row:
            return self._row_to_product(row)
        return None
    
    def get_all_products(self, active_only: bool = True) -> List[Product]:
        """Get all products"""
        cursor = self.conn.cursor()
        
        query = "SELECT * FROM products"
        if active_only:
            query += " WHERE active = 1"
        query += " ORDER BY created_at DESC"
        
        cursor.execute(query)
        rows = cursor.fetchall()
        
        return [self._row_to_product(row) for row in rows]
    
    def update_product_price(self, product_id: int, price: float) -> bool:
        """Update product's current price"""
        cursor = self.conn.cursor()
        
        # Get current product
        product = self.get_product(product_id)
        if not product:
            return False
        
        # Update lowest/highest prices
        lowest = min(product.lowest_price or price, price)
        highest = max(product.highest_price or price, price)
        
        cursor.execute("""
            UPDATE products 
            SET current_price = ?, lowest_price = ?, highest_price = ?, last_checked = ?
            WHERE id = ?
        """, (price, lowest, highest, datetime.now(), product_id))
        
        self.conn.commit()
        
        self.logger.info(f"Updated price for product {product_id}: ${price:.2f}")
        return cursor.rowcount > 0
    
    def update_target_price(self, product_id: int, target_price: float) -> bool:
        """Update product's target price"""
        cursor = self.conn.cursor()
        
        cursor.execute("""
            UPDATE products SET target_price = ? WHERE id = ?
        """, (target_price, product_id))
        
        self.conn.commit()
        return cursor.rowcount > 0
    
    def remove_product(self, product_id: int) -> bool:
        """Soft delete a product"""
        cursor = self.conn.cursor()
        
        cursor.execute("""
            UPDATE products SET active = 0 WHERE id = ?
        """, (product_id,))
        
        self.conn.commit()
        return cursor.rowcount > 0
    
    def add_price_history(self, product_id: int, price: float, 
                         availability: Optional[str] = None,
                         seller: Optional[str] = None,
                         notes: Optional[str] = None) -> int:
        """Add a price history record"""
        cursor = self.conn.cursor()
        
        cursor.execute("""
            INSERT INTO price_history (product_id, price, availability, seller, notes)
            VALUES (?, ?, ?, ?, ?)
        """, (product_id, price, availability, seller, notes))
        
        self.conn.commit()
        return cursor.lastrowid
    
    def get_price_history(self, product_id: int, 
                         since: Optional[datetime] = None,
                         limit: int = 100) -> List[PriceHistory]:
        """Get price history for a product"""
        cursor = self.conn.cursor()
        
        query = "SELECT * FROM price_history WHERE product_id = ?"
        params = [product_id]
        
        if since:
            query += " AND checked_at >= ?"
            params.append(since)
        
        query += " ORDER BY checked_at DESC LIMIT ?"
        params.append(limit)
        
        cursor.execute(query, params)
        rows = cursor.fetchall()
        
        return [self._row_to_price_history(row) for row in rows]
    
    def add_notification(self, notification: Notification) -> int:
        """Add a notification record"""
        cursor = self.conn.cursor()
        
        cursor.execute("""
            INSERT INTO notifications (product_id, type, price_at_notification, message, success)
            VALUES (?, ?, ?, ?, ?)
        """, (notification.product_id, notification.type, 
              notification.price_at_notification, notification.message, notification.success))
        
        # Update product's last notification time
        cursor.execute("""
            UPDATE products SET notification_sent_at = CURRENT_TIMESTAMP WHERE id = ?
        """, (notification.product_id,))
        
        self.conn.commit()
        return cursor.lastrowid
    
    def should_send_notification(self, product_id: int, cooldown_hours: int = 24) -> bool:
        """Check if enough time has passed since last notification"""
        cursor = self.conn.cursor()
        
        cursor.execute("""
            SELECT notification_sent_at FROM products WHERE id = ?
        """, (product_id,))
        
        row = cursor.fetchone()
        if not row or not row['notification_sent_at']:
            return True
        
        last_sent = datetime.fromisoformat(row['notification_sent_at'])
        cooldown = timedelta(hours=cooldown_hours)
        
        return datetime.now() - last_sent > cooldown
    
    def get_products_to_check(self, check_interval_minutes: int = 60) -> List[Product]:
        """Get products that need price checking"""
        cursor = self.conn.cursor()
        
        cutoff_time = datetime.now() - timedelta(minutes=check_interval_minutes)
        
        cursor.execute("""
            SELECT * FROM products 
            WHERE active = 1 
            AND (last_checked IS NULL OR last_checked < ?)
            ORDER BY last_checked ASC
        """, (cutoff_time,))
        
        rows = cursor.fetchall()
        return [self._row_to_product(row) for row in rows]
    
    def _row_to_product(self, row: sqlite3.Row) -> Product:
        """Convert database row to Product object"""
        return Product(
            id=row['id'],
            url=row['url'],
            name=row['name'],
            site=row['site'],
            target_price=row['target_price'],
            current_price=row['current_price'],
            lowest_price=row['lowest_price'],
            highest_price=row['highest_price'],
            last_checked=datetime.fromisoformat(row['last_checked']) if row['last_checked'] else None,
            created_at=datetime.fromisoformat(row['created_at']) if row['created_at'] else None,
            updated_at=datetime.fromisoformat(row['updated_at']) if row['updated_at'] else None,
            active=bool(row['active']),
            notification_sent_at=datetime.fromisoformat(row['notification_sent_at']) if row['notification_sent_at'] else None
        )
    
    def _row_to_price_history(self, row: sqlite3.Row) -> PriceHistory:
        """Convert database row to PriceHistory object"""
        return PriceHistory(
            id=row['id'],
            product_id=row['product_id'],
            price=row['price'],
            checked_at=datetime.fromisoformat(row['checked_at']),
            availability=row['availability'],
            seller=row['seller'],
            notes=row['notes']
        )
    
    def close(self):
        """Close database connection"""
        if self.conn:
            self.conn.close()
    
    def __enter__(self):
        return self
    
    def __exit__(self, exc_type, exc_val, exc_tb):
        self.close()
