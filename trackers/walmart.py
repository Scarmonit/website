"""
Walmart price tracker implementation
"""

from typing import Dict, Optional, Any
from bs4 import BeautifulSoup

from .base import TrackerBase


class WalmartTracker(TrackerBase):
    """Price tracker for Walmart products"""
    
    @property
    def site_name(self) -> str:
        return "Walmart"
    
    def extract_product_info(self, soup: BeautifulSoup, url: str) -> Optional[Dict[str, Any]]:
        """Extract product information from Walmart page"""
        
        # Multiple price selectors for different page layouts
        price_selectors = [
            # Current price displays
            {'selector': 'span[itemprop="price"]', 'method': 'css'},
            {'selector': 'span.inline-flex.flex-column span.f1', 'method': 'css'},
            {'selector': 'span[data-automation-id="product-price"]', 'method': 'css'},
            {'selector': 'div[data-testid="price-wrap"] span', 'method': 'css'},
            # Price with currency
            {'selector': 'span.price-main span.price-characteristic', 'method': 'css'},
            {'selector': 'div.price-main', 'method': 'css'},
            # Sale price
            {'selector': 'span.reduced', 'method': 'css'},
            {'selector': 'div.prod-PriceSection span', 'method': 'css'},
            # Alternative layouts
            {'selector': 'span.visuallyhidden:contains("current price")', 'method': 'css'},
            {'selector': 'meta[property="product:price:amount"]', 'method': 'css', 'attribute': 'content'},
            # Mobile view
            {'selector': 'div.valign-middle span', 'method': 'css'},
        ]
        
        # Title selectors
        title_selectors = [
            {'selector': 'h1[itemprop="name"]', 'method': 'css'},
            {'selector': 'h1.prod-ProductTitle', 'method': 'css'},
            {'selector': 'h1[data-automation-id="product-title"]', 'method': 'css'},
            {'selector': 'h1.f3', 'method': 'css'},
            {'selector': 'div.prod-title h1', 'method': 'css'},
        ]
        
        # Try to extract price
        price_text = self.try_selectors(soup, price_selectors)
        
        # Check for out of stock
        if not price_text:
            oos_selectors = [
                {'selector': 'div[data-testid="out-of-stock"]', 'method': 'css'},
                {'selector': 'button[aria-label*="Out of stock"]', 'method': 'css'},
                {'selector': 'span:contains("Out of stock")', 'method': 'css'},
            ]
            oos = self.try_selectors(soup, oos_selectors)
            if oos:
                self.logger.warning(f"Product out of stock: {url}")
                return None
        
        # Extract title
        title = self.try_selectors(soup, title_selectors)
        
        # Extract image
        image_selectors = [
            {'selector': 'img[data-testid="hero-image"]', 'method': 'css', 'attribute': 'src'},
            {'selector': 'div.prod-hero-image img', 'method': 'css', 'attribute': 'src'},
            {'selector': 'img[itemprop="image"]', 'method': 'css', 'attribute': 'src'},
            {'selector': 'div.hover-zoom-hero-image img', 'method': 'css', 'attribute': 'src'},
        ]
        image_url = self.try_selectors(soup, image_selectors)
        
        # Extract availability
        availability_selectors = [
            {'selector': 'div[data-testid="fulfillment-badge"] span', 'method': 'css'},
            {'selector': 'div.prod-fulfillment-messaging span', 'method': 'css'},
            {'selector': 'span[data-automation-id="in-stock"]', 'method': 'css'},
        ]
        availability = self.try_selectors(soup, availability_selectors)
        
        # Extract seller
        seller_selectors = [
            {'selector': 'a[data-testid="seller-name"]', 'method': 'css'},
            {'selector': 'div.sold-shipped-by a', 'method': 'css'},
            {'selector': 'span.sold-by-text', 'method': 'css'},
        ]
        seller = self.try_selectors(soup, seller_selectors)
        
        # Check for rollback/clearance
        rollback_selectors = [
            {'selector': 'span[data-automation-id="rollback"]', 'method': 'css'},
            {'selector': 'div.prod-price-tag', 'method': 'css'},
            {'selector': 'span.reduced-tag', 'method': 'css'},
        ]
        has_rollback = self.try_selectors(soup, rollback_selectors) is not None
        
        # Extract rating
        rating_selectors = [
            {'selector': 'span[itemprop="ratingValue"]', 'method': 'css'},
            {'selector': 'div.rating span.rating-number', 'method': 'css'},
            {'selector': 'span[data-testid="rating-stars"]', 'method': 'css', 'attribute': 'aria-label'},
        ]
        rating = self.try_selectors(soup, rating_selectors)
        
        # Extract number of reviews
        review_count_selectors = [
            {'selector': 'span[itemprop="reviewCount"]', 'method': 'css'},
            {'selector': 'a[data-testid="reviews-link"] span', 'method': 'css'},
            {'selector': 'span.prod-reviews-count', 'method': 'css'},
        ]
        review_count = self.try_selectors(soup, review_count_selectors)
        
        result = {
            'title': title or 'Unknown Product',
            'price_text': price_text,
            'image_url': image_url,
            'availability': availability,
            'seller': seller or 'Walmart',
            'has_rollback': has_rollback,
            'rating': rating,
            'review_count': review_count,
            'url': url
        }
        
        self.logger.info(f"Extracted Walmart product: {result.get('title')[:50]}... - Price: {price_text}")
        
        return result
