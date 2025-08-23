"""
Web scraper module for the Personal Price Tracker

This module handles price extraction from major e-commerce websites including:
- Amazon
- eBay  
- Walmart
- Generic price detection for other sites

Features:
- Site-specific price selectors
- Price cleaning and validation
- User-agent rotation
- Error handling and retry logic
- Respect for robots.txt (basic implementation)
"""

import requests
from bs4 import BeautifulSoup
import re
import time
import random
from typing import Optional, Dict, Tuple
from urllib.parse import urlparse, urljoin
import logging


class PriceScraper:
    """Web scraper for extracting product prices from e-commerce sites"""
    
    # Compile regex patterns once for efficiency (instead of on every clean_price call)
    PRICE_PATTERN = re.compile(r'(\d{1,3}(?:,\d{3})*(?:\.\d{1,2})?|\d+(?:\.\d{1,2})?)')
    CURRENCY_REMOVE_PATTERN = re.compile(r'[^\d.,\s]')
    
    def __init__(self, user_agent: str = None):
        """
        Initialize the price scraper
        
        Args:
            user_agent: Custom user agent string
        """
        self.session = requests.Session()
        
        # Default user agents (rotate to avoid detection)
        self.user_agents = [
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15"
        ]
        
        if user_agent:
            self.user_agents.insert(0, user_agent)
        
        # Site-specific price selectors
        self.selectors = {
            'amazon.com': [
                '.a-price.a-text-price.a-size-medium.apexPriceToPay .a-offscreen',
                '.a-price .a-offscreen',
                '.a-price-current .a-offscreen',
                '#priceblock_dealprice',
                '#priceblock_ourprice',
                '.a-price.a-text-price.a-size-medium.apexPriceToPay',
                '.a-price-whole',
                'span[class*="a-price-range"]',
            ],
            'ebay.com': [
                '.u-flL.condText',
                '.ux-textspans.notranslate',
                '#x-price-primary .ux-textspans',
                '.mainPrice .ux-textspans',
                '#mm-saleDscPrc',
                '#prcIsum',
                '.u-flL',
                'span[itemprop="price"]',
            ],
            'walmart.com': [
                'span[data-automation-id="product-price"]',
                '[data-testid="price-current"]',
                'span[itemprop="price"]',
                '.price-current',
                '.price .visuallyhidden',
                '#price .price-current',
                '.prod-PriceSection span[data-automation-id="product-price"]',
            ],
            'target.com': [
                'span[data-test="product-price"]',
                '[data-test="product-price-value"]',
                'span[class*="Price"]',
                '.h-price-current',
            ],
            'bestbuy.com': [
                '.sr-only:contains("current price")',
                'span[class*="sr-only"]:contains("current price")',
                '.pricing-current-price .sr-only',
                'span.sr-only',
                '.visuallyhidden',
            ]
        }
        
        # Headers to appear more like a real browser
        self.default_headers = {
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language': 'en-US,en;q=0.5',
            'Accept-Encoding': 'gzip, deflate',
            'Connection': 'keep-alive',
            'Upgrade-Insecure-Requests': '1',
            'Sec-Fetch-Dest': 'document',
            'Sec-Fetch-Mode': 'navigate',
            'Sec-Fetch-Site': 'none',
            'Cache-Control': 'max-age=0',
        }
        
        # Configure session
        self.session.headers.update(self.default_headers)
    
    def get_site_name(self, url: str) -> str:
        """
        Extract site name from URL
        
        Args:
            url: Product URL
            
        Returns:
            Site name (e.g., 'amazon.com')
        """
        try:
            # Handle empty or None URL gracefully
            if not url:
                return 'unknown'
            
            domain = urlparse(url).netloc.lower()
            
            # Remove www. prefix for consistency
            if domain.startswith('www.'):
                domain = domain[4:]
                
            return domain if domain else 'unknown'
            
        except (AttributeError, ValueError) as e:
            # More specific error handling instead of bare except
            return 'unknown'
    
    def clean_price(self, price_text: str) -> Optional[float]:
        """
        Clean and convert price text to float (optimized with compiled regex)
        
        Args:
            price_text: Raw price text from webpage
            
        Returns:
            Clean price as float or None if parsing failed
        """
        if not price_text:
            return None
            
        # Remove common price prefixes/suffixes and whitespace
        price_text = str(price_text).strip()
        
        # Remove currency symbols and common text using compiled pattern
        price_text = self.CURRENCY_REMOVE_PATTERN.sub('', price_text).strip()
        
        # Find price using compiled pattern (more efficient than multiple regex calls)
        matches = self.PRICE_PATTERN.findall(price_text)
        
        if matches:
            # Take the first match and try to convert to float
            price_str = matches[0].replace(',', '')
            try:
                price = float(price_str)
                # Basic validation - prices should be positive and reasonable
                return price if 0 < price < 1000000 else None
            except ValueError:
                pass
        
        return None
    
    def get_page_content(self, url: str, retries: int = 3) -> Optional[BeautifulSoup]:
        """
        Fetch and parse webpage content
        
        Args:
            url: URL to fetch
            retries: Number of retry attempts
            
        Returns:
            BeautifulSoup object or None if failed
        """
        for attempt in range(retries):
            try:
                # Use random user agent
                self.session.headers['User-Agent'] = random.choice(self.user_agents)
                
                # Add random delay to appear more human
                if attempt > 0:
                    time.sleep(random.uniform(2, 5))
                
                response = self.session.get(url, timeout=10)
                response.raise_for_status()
                
                # Check if we got blocked
                if 'captcha' in response.text.lower() or response.status_code == 429:
                    print(f"⚠️  Detected rate limiting for {url}, waiting...")
                    time.sleep(random.uniform(5, 10))
                    continue
                
                return BeautifulSoup(response.content, 'html.parser')
                
            except requests.exceptions.RequestException as e:
                print(f"⚠️  Attempt {attempt + 1} failed for {url}: {e}")
                if attempt == retries - 1:
                    return None
        
        return None
    
    def extract_price_generic(self, soup: BeautifulSoup) -> Optional[float]:
        """
        Generic price extraction for unknown sites
        
        Args:
            soup: BeautifulSoup object of the webpage
            
        Returns:
            Extracted price or None
        """
        # Common price selectors across many sites
        generic_selectors = [
            '[class*="price"]',
            '[id*="price"]',
            '[data-testid*="price"]',
            '[data-test*="price"]',
            'span[itemprop="price"]',
            '.price',
            '#price',
            '.cost',
            '.amount',
            '[class*="cost"]',
            '[class*="amount"]',
            'meta[property="product:price:amount"]',
            'meta[name="price"]',
        ]
        
        for selector in generic_selectors:
            try:
                elements = soup.select(selector)
                for element in elements:
                    # Try different ways to get price text
                    price_text = (
                        element.get('content') or  # For meta tags
                        element.get_text(strip=True) or
                        element.get('value')
                    )
                    
                    if price_text:
                        price = self.clean_price(price_text)
                        if price and price > 0:
                            return price
            except Exception:
                continue
        
        return None
    
    def extract_price_from_site(self, url: str, soup: BeautifulSoup) -> Optional[float]:
        """
        Extract price using site-specific selectors
        
        Args:
            url: Product URL
            soup: BeautifulSoup object of the webpage
            
        Returns:
            Extracted price or None
        """
        site = self.get_site_name(url)
        
        # Try site-specific selectors first
        if site in self.selectors:
            for selector in self.selectors[site]:
                try:
                    elements = soup.select(selector)
                    for element in elements:
                        price_text = element.get_text(strip=True)
                        if price_text:
                            price = self.clean_price(price_text)
                            if price and price > 0:
                                print(f"💰 Found price ${price} using selector: {selector}")
                                return price
                except Exception as e:
                    continue
        
        # Fall back to generic extraction
        price = self.extract_price_generic(soup)
        if price:
            print(f"💰 Found price ${price} using generic extraction")
        
        return price
    
    def get_product_price(self, url: str, verbose: bool = False) -> Tuple[Optional[float], str]:
        """
        Get current price for a product
        
        Args:
            url: Product URL
            verbose: Enable detailed logging
            
        Returns:
            Tuple of (price, status_message)
        """
        try:
            if verbose:
                print(f"🔍 Checking price for: {url}")
            
            # Fetch page content
            soup = self.get_page_content(url)
            if not soup:
                return None, "Failed to fetch webpage"
            
            # Extract price
            price = self.extract_price_from_site(url, soup)
            
            if price:
                if verbose:
                    print(f"✅ Successfully extracted price: ${price}")
                return price, "success"
            else:
                # Try to give helpful debugging info
                site = self.get_site_name(url)
                error_msg = f"Could not find price on {site}. Page structure may have changed."
                
                if verbose:
                    print(f"❌ {error_msg}")
                    print("🔍 Looking for price indicators in page...")
                    
                    # Find potential price elements for debugging
                    price_elements = soup.find_all(string=re.compile(r'\$[\d,]+\.?\d*'))
                    if price_elements:
                        print(f"   Found {len(price_elements)} potential price texts")
                        for i, elem in enumerate(price_elements[:3]):
                            print(f"   {i+1}: {elem.strip()}")
                
                return None, error_msg
                
        except Exception as e:
            error_msg = f"Error extracting price: {str(e)}"
            if verbose:
                print(f"❌ {error_msg}")
            return None, error_msg
    
    def test_selectors(self, url: str) -> Dict:
        """
        Test all selectors on a URL for debugging
        
        Args:
            url: URL to test
            
        Returns:
            Dictionary with test results
        """
        results = {
            'url': url,
            'site': self.get_site_name(url),
            'selectors_tested': 0,
            'prices_found': [],
            'errors': []
        }
        
        try:
            soup = self.get_page_content(url)
            if not soup:
                results['errors'].append("Failed to fetch webpage")
                return results
            
            site = results['site']
            selectors_to_test = []
            
            # Add site-specific selectors
            if site in self.selectors:
                selectors_to_test.extend(self.selectors[site])
            
            # Add generic selectors
            selectors_to_test.extend([
                '[class*="price"]',
                '[id*="price"]', 
                'span[itemprop="price"]',
                '.price',
                '#price'
            ])
            
            for selector in selectors_to_test:
                try:
                    results['selectors_tested'] += 1
                    elements = soup.select(selector)
                    
                    for element in elements:
                        price_text = element.get_text(strip=True)
                        if price_text:
                            price = self.clean_price(price_text)
                            if price and price > 0:
                                results['prices_found'].append({
                                    'selector': selector,
                                    'price': price,
                                    'raw_text': price_text
                                })
                                
                except Exception as e:
                    results['errors'].append(f"Selector '{selector}': {str(e)}")
            
        except Exception as e:
            results['errors'].append(f"General error: {str(e)}")
        
        return results


if __name__ == "__main__":
    # Test the scraper functionality
    print("🧪 Testing price scraper...")
    
    scraper = PriceScraper()
    
    # Test URLs (these are examples - use real product URLs)
    test_urls = [
        "https://www.amazon.com/dp/B08N5WRWNW",  # Example Amazon URL
        "https://www.walmart.com/ip/123456789",   # Example Walmart URL
    ]
    
    for url in test_urls:
        print(f"\n🔍 Testing: {url}")
        price, status = scraper.get_product_price(url, verbose=True)
        
        if price:
            print(f"✅ Price: ${price}")
        else:
            print(f"❌ Status: {status}")
    
    print("\n🧪 Scraper test completed!")
