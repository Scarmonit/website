"""
Amazon price tracker implementation
"""

from typing import Dict, Optional, Any
from bs4 import BeautifulSoup

from .base import TrackerBase


class AmazonTracker(TrackerBase):
    """Price tracker for Amazon products"""
    
    @property
    def site_name(self) -> str:
        return "Amazon"
    
    def extract_product_info(self, soup: BeautifulSoup, url: str) -> Optional[Dict[str, Any]]:
        """Extract product information from Amazon page"""
        
        # Multiple price selectors for different page layouts
        price_selectors = [
            {'selector': 'span.a-price-whole', 'method': 'css'},
            {'selector': 'span.a-price.a-text-price.a-size-medium.apexPriceToPay', 'method': 'css'},
            {'selector': 'span.a-price-current', 'method': 'css'},
            {'selector': 'span#priceblock_dealprice', 'method': 'css'},
            {'selector': 'span#priceblock_ourprice', 'method': 'css'},
            {'selector': 'span.a-price.a-text-price', 'method': 'css'},
            {'selector': 'span.a-color-price', 'method': 'css'},
            {'selector': 'span.offer-price', 'method': 'css'},
            {'selector': '.a-price-range span.a-price', 'method': 'css'},
            # Buy Box price
            {'selector': 'div#apex_desktop span.a-price-whole', 'method': 'css'},
            # Used price fallback
            {'selector': 'span.a-size-base.a-color-price', 'method': 'css'},
            # Deal price
            {'selector': 'span.deal-price', 'method': 'css'},
            # Lightning deal
            {'selector': 'span.priceBlockDealPriceString', 'method': 'css'},
        ]
        
        # Title selectors
        title_selectors = [
            {'selector': 'span#productTitle', 'method': 'css'},
            {'selector': 'h1.a-size-large', 'method': 'css'},
            {'selector': 'h1#title', 'method': 'css'},
            {'selector': 'h1[itemprop="name"]', 'method': 'css'},
            {'selector': 'div#title_feature_div h1', 'method': 'css'},
        ]
        
        # Try to extract price
        price_text = self.try_selectors(soup, price_selectors)
        
        # If no price found, check for "Currently unavailable"
        if not price_text:
            unavailable = soup.select_one('div#availability span.a-color-price')
            if unavailable and 'unavailable' in unavailable.get_text().lower():
                self.logger.warning(f"Product currently unavailable: {url}")
                return None
        
        # Extract title
        title = self.try_selectors(soup, title_selectors)
        
        # Extract image
        image_selectors = [
            {'selector': 'img#landingImage', 'method': 'css', 'attribute': 'src'},
            {'selector': 'div#imgTagWrapperId img', 'method': 'css', 'attribute': 'src'},
            {'selector': 'img.a-dynamic-image', 'method': 'css', 'attribute': 'src'},
        ]
        image_url = self.try_selectors(soup, image_selectors)
        
        # Extract availability
        availability_selectors = [
            {'selector': 'div#availability span', 'method': 'css'},
            {'selector': 'span.a-size-medium.a-color-success', 'method': 'css'},
            {'selector': 'div#availability_feature_div span', 'method': 'css'},
        ]
        availability = self.try_selectors(soup, availability_selectors)
        
        # Extract seller
        seller_selectors = [
            {'selector': 'a#bylineInfo', 'method': 'css'},
            {'selector': 'div.tabular-buybox-text a', 'method': 'css'},
            {'selector': 'span.a-size-small.offer-display-feature-text', 'method': 'css'},
        ]
        seller = self.try_selectors(soup, seller_selectors)
        
        # Check for deals/discounts
        deal_badge = soup.select_one('span.dealBadge, div.dealBadge')
        has_deal = deal_badge is not None
        
        result = {
            'title': title or 'Unknown Product',
            'price_text': price_text,
            'image_url': image_url,
            'availability': availability,
            'seller': seller,
            'has_deal': has_deal,
            'url': url
        }
        
        self.logger.info(f"Extracted Amazon product: {result.get('title')[:50]}... - Price: {price_text}")
        
        return result
