"""
Tests for database models
"""

import pytest
import tempfile
import os
from datetime import datetime, timedelta
from pathlib import Path

from db.models import Database, Product, PriceHistory


class TestDatabase:
    """Test database operations"""
    
    @pytest.fixture
    def temp_db(self):
        """Create temporary database for testing"""
        temp_file = tempfile.NamedTemporaryFile(delete=False, suffix='.db')
        temp_file.close()
        
        db = Database(temp_file.name)
        db.init_database()
        
        yield db
        
        db.close()
        os.unlink(temp_file.name)
    
    def test_init_database(self, temp_db):
        """Test database initialization"""
        # Check that tables exist
        cursor = temp_db.conn.cursor()
        cursor.execute("SELECT name FROM sqlite_master WHERE type='table'")
        tables = [row[0] for row in cursor.fetchall()]
        
        assert 'products' in tables
        assert 'price_history' in tables
        assert 'notifications' in tables
    
    def test_add_product(self, temp_db):
        """Test adding a product"""
        product = Product(
            url="https://example.com/product",
            name="Test Product",
            site="Example",
            target_price=50.00,
            current_price=75.00
        )
        
        product_id = temp_db.add_product(product)
        assert product_id > 0
        
        # Verify product was added
        retrieved = temp_db.get_product(product_id)
        assert retrieved is not None
        assert retrieved.name == "Test Product"
        assert retrieved.target_price == 50.00
    
    def test_update_product_price(self, temp_db):
        """Test updating product price"""
        # Add product
        product = Product(
            url="https://example.com/product",
            name="Test Product",
            site="Example",
            target_price=50.00
        )
        product_id = temp_db.add_product(product)
        
        # Update price
        temp_db.update_product_price(product_id, 45.00)
        
        # Verify update
        retrieved = temp_db.get_product(product_id)
        assert retrieved.current_price == 45.00
        assert retrieved.lowest_price == 45.00
        assert retrieved.highest_price == 45.00
        
        # Update with higher price
        temp_db.update_product_price(product_id, 60.00)
        retrieved = temp_db.get_product(product_id)
        assert retrieved.current_price == 60.00
        assert retrieved.lowest_price == 45.00
        assert retrieved.highest_price == 60.00
    
    def test_price_history(self, temp_db):
        """Test price history tracking"""
        # Add product
        product = Product(
            url="https://example.com/product",
            name="Test Product",
            site="Example",
            target_price=50.00
        )
        product_id = temp_db.add_product(product)
        
        # Add price history (should be ordered by most recent first)
        temp_db.add_price_history(product_id, 75.00, availability="In Stock")
        temp_db.add_price_history(product_id, 65.00, availability="In Stock")
        temp_db.add_price_history(product_id, 45.00, availability="Limited")
        
        # Get history
        history = temp_db.get_price_history(product_id)
        assert len(history) == 3
        assert history[0].price == 45.00  # Most recent first (highest id)
        assert history[2].price == 75.00  # Oldest last (lowest id)
    
    def test_notification_cooldown(self, temp_db):
        """Test notification cooldown logic"""
        # Add product
        product = Product(
            url="https://example.com/product",
            name="Test Product",
            site="Example",
            target_price=50.00
        )
        product_id = temp_db.add_product(product)
        
        # Should send notification (no previous notification)
        assert temp_db.should_send_notification(product_id, cooldown_hours=24) is True
        
        # Add notification
        from db.models import Notification
        notification = Notification(
            product_id=product_id,
            type="email",
            sent_at=datetime.now(),
            price_at_notification=45.00
        )
        temp_db.add_notification(notification)
        
        # Should not send (within cooldown)
        assert temp_db.should_send_notification(product_id, cooldown_hours=24) is False
        
        # Should send with shorter cooldown
        assert temp_db.should_send_notification(product_id, cooldown_hours=0) is True
    
    def test_get_products_to_check(self, temp_db):
        """Test getting products that need checking"""
        # Add products with different last_checked times
        now = datetime.now()
        
        # Product 1: Never checked
        product1 = Product(
            url="https://example.com/product1",
            name="Product 1",
            site="Example",
            target_price=50.00
        )
        id1 = temp_db.add_product(product1)
        
        # Product 2: Checked recently
        product2 = Product(
            url="https://example.com/product2",
            name="Product 2",
            site="Example",
            target_price=50.00,
            current_price=60.00
        )
        id2 = temp_db.add_product(product2)
        temp_db.update_product_price(id2, 60.00)
        
        # Product 3: Checked long ago (manually set)
        product3 = Product(
            url="https://example.com/product3",
            name="Product 3",
            site="Example",
            target_price=50.00
        )
        id3 = temp_db.add_product(product3)
        
        # Manually update last_checked for product3
        old_time = now - timedelta(hours=2)
        cursor = temp_db.conn.cursor()
        cursor.execute("UPDATE products SET last_checked = ? WHERE id = ?", 
                      (old_time, id3))
        temp_db.conn.commit()
        
        # Get products to check (60 minute interval)
        products = temp_db.get_products_to_check(check_interval_minutes=60)
        
        # Should get product1 (never checked) and product3 (checked 2 hours ago)
        product_ids = [p.id for p in products]
        assert id1 in product_ids
        assert id2 not in product_ids  # Recently checked
        assert id3 in product_ids
    
    def test_remove_product(self, temp_db):
        """Test removing a product (soft delete)"""
        # Add product
        product = Product(
            url="https://example.com/product",
            name="Test Product",
            site="Example",
            target_price=50.00
        )
        product_id = temp_db.add_product(product)
        
        # Remove product
        assert temp_db.remove_product(product_id) is True
        
        # Should not be retrievable
        assert temp_db.get_product(product_id) is None
        
        # Should not appear in active products
        products = temp_db.get_all_products(active_only=True)
        assert len(products) == 0
