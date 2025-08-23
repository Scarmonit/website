"""
eBay price tracker implementation
"""

from typing import Dict, Optional, Any
from bs4 import BeautifulSoup

from .base import TrackerBase


class EbayTracker(TrackerBase):
    """Price tracker for eBay products"""
    
    @property
    def site_name(self) -> str:
        return "eBay"
    
    def extract_product_info(self, soup: BeautifulSoup, url: str) -> Optional[Dict[str, Any]]:
        """Extract product information from eBay page"""
        
        # Multiple price selectors for different page layouts
        price_selectors = [
            # Buy It Now price
            {'selector': 'span.ux-textspans--BOLD', 'method': 'css'},
            {'selector': 'div.x-price-primary span.ux-textspans', 'method': 'css'},
            {'selector': 'div.u-flL span.notranslate', 'method': 'css'},
            # Current bid price
            {'selector': 'div.u-flL.u-tar.vi-acc-del-range__price span', 'method': 'css'},
            {'selector': 'span[itemprop="price"]', 'method': 'css'},
            # Legacy selectors
            {'selector': 'span#prcIsum', 'method': 'css'},
            {'selector': 'span#mm-saleDscPrc', 'method': 'css'},
            # Mobile view
            {'selector': 'div.display-price', 'method': 'css'},
            {'selector': 'h2.it-ttl span.it-price', 'method': 'css'},
            # Offer price
            {'selector': 'div.u-flL.w29.vi-acc-del-range__price span', 'method': 'css'},
            # Best offer
            {'selector': 'div.vi-cc-exp-txt span.vi-acc-del-range__price', 'method': 'css'},
        ]
        
        # Title selectors
        title_selectors = [
            {'selector': 'h1.it-ttl', 'method': 'css'},
            {'selector': 'h1[itemprop="name"]', 'method': 'css'},
            {'selector': 'h1.v-textbox-title', 'method': 'css'},
            {'selector': 'div.u-dspn h1', 'method': 'css'},
            {'selector': 'h1[data-testid="listing-title"]', 'method': 'css'},
        ]
        
        # Try to extract price
        price_text = self.try_selectors(soup, price_selectors)
        
        # Check if auction ended
        if not price_text:
            ended = soup.select_one('span.vi-end-lb')
            if ended and 'ended' in ended.get_text().lower():
                self.logger.warning(f"Auction has ended: {url}")
                # Try to get final price
                final_price_selectors = [
                    {'selector': 'span.vi-qtyS-hot-red', 'method': 'css'},
                    {'selector': 'span.sold-price', 'method': 'css'},
                ]
                price_text = self.try_selectors(soup, final_price_selectors)
        
        # Extract title
        title = self.try_selectors(soup, title_selectors)
        
        # Extract image
        image_selectors = [
            {'selector': 'img#icImg', 'method': 'css', 'attribute': 'src'},
            {'selector': 'div.ux-image-carousel img', 'method': 'css', 'attribute': 'src'},
            {'selector': 'div.u-flL.ict-100 img', 'method': 'css', 'attribute': 'src'},
            {'selector': 'img[itemprop="image"]', 'method': 'css', 'attribute': 'src'},
        ]
        image_url = self.try_selectors(soup, image_selectors)
        
        # Extract condition
        condition_selectors = [
            {'selector': 'div.u-flL.condText', 'method': 'css'},
            {'selector': 'span[data-testid="condition-value"]', 'method': 'css'},
            {'selector': 'div.vi-itm-cond', 'method': 'css'},
        ]
        condition = self.try_selectors(soup, condition_selectors)
        
        # Extract seller
        seller_selectors = [
            {'selector': 'span.mbg-nw', 'method': 'css'},
            {'selector': 'a.mbg-id', 'method': 'css'},
            {'selector': 'span[data-testid="seller-name"]', 'method': 'css'},
        ]
        seller = self.try_selectors(soup, seller_selectors)
        
        # Check for shipping
        shipping_selectors = [
            {'selector': 'span#vi-acc-del-fee', 'method': 'css'},
            {'selector': 'span.vi-ship-free', 'method': 'css'},
            {'selector': 'span[data-testid="shipping-cost"]', 'method': 'css'},
        ]
        shipping = self.try_selectors(soup, shipping_selectors)
        
        # Check if it's an auction or Buy It Now
        is_auction = bool(soup.select_one('span.vi-VR-btnWr button[id*="bidBtn"]'))
        
        # Time left for auction
        time_left = None
        if is_auction:
            time_selectors = [
                {'selector': 'span.vi-tm-left', 'method': 'css'},
                {'selector': 'span.timeMs', 'method': 'css'},
            ]
            time_left = self.try_selectors(soup, time_selectors)
        
        result = {
            'title': title or 'Unknown Product',
            'price_text': price_text,
            'image_url': image_url,
            'condition': condition,
            'seller': seller,
            'shipping': shipping,
            'is_auction': is_auction,
            'time_left': time_left,
            'url': url
        }
        
        self.logger.info(f"Extracted eBay product: {result.get('title')[:50]}... - Price: {price_text}")
        
        return result
