"""
Base tracker abstract class for all price scrapers
"""

from abc import ABC, abstractmethod
from typing import Dict, Optional, List, Any
import time
import random
from configparser import ConfigParser

import requests
from bs4 import BeautifulSoup
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry

from services.logger import setup_logger
from utils.parsing import clean_text, parse_currency


class TrackerBase(ABC):
    """Abstract base class for price tracking scrapers"""
    
    def __init__(self):
        self.logger = setup_logger(self.__class__.__name__)
        self.config = self._load_config()
        self.session = self._create_session()
        
    def _load_config(self) -> ConfigParser:
        """Load configuration from config.ini"""
        config = ConfigParser()
        config.read('config.ini')
        return config
    
    def _create_session(self) -> requests.Session:
        """Create requests session with retry strategy"""
        session = requests.Session()
        
        # Configure retries
        retry_strategy = Retry(
            total=int(self.config.get('scraping', 'max_retries', fallback='3')),
            backoff_factor=float(self.config.get('scraping', 'retry_backoff', fallback='2')),
            status_forcelist=[429, 500, 502, 503, 504],
        )
        
        adapter = HTTPAdapter(max_retries=retry_strategy)
        session.mount("http://", adapter)
        session.mount("https://", adapter)
        
        # Set user agent
        session.headers.update({
            'User-Agent': self.config.get('scraping', 'user_agent'),
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language': 'en-US,en;q=0.5',
            'Accept-Encoding': 'gzip, deflate',
            'Connection': 'keep-alive',
            'Upgrade-Insecure-Requests': '1',
        })
        
        return session
    
    def fetch_page(self, url: str) -> Optional[BeautifulSoup]:
        """
        Fetch and parse a webpage
        
        Args:
            url: URL to fetch
            
        Returns:
            BeautifulSoup object or None on failure
        """
        try:
            # Add random delay to avoid rate limiting
            delay = random.uniform(1, 3)
            time.sleep(delay)
            
            timeout = int(self.config.get('scraping', 'timeout', fallback='30'))
            response = self.session.get(url, timeout=timeout)
            response.raise_for_status()
            
            return BeautifulSoup(response.content, 'html.parser')
            
        except requests.RequestException as e:
            self.logger.error(f"Failed to fetch {url}: {e}")
            return None
    
    def try_selectors(self, soup: BeautifulSoup, selectors: List[Dict[str, Any]]) -> Optional[str]:
        """
        Try multiple selectors to extract content
        
        Args:
            soup: BeautifulSoup object
            selectors: List of selector configurations
            
        Returns:
            Extracted text or None
        """
        for selector_config in selectors:
            try:
                selector = selector_config['selector']
                method = selector_config.get('method', 'css')
                attribute = selector_config.get('attribute')
                
                if method == 'css':
                    element = soup.select_one(selector)
                elif method == 'xpath':
                    # Note: BeautifulSoup doesn't support XPath directly
                    # Using CSS selector fallback
                    element = soup.select_one(selector)
                else:
                    element = soup.find(selector_config.get('tag'), 
                                       class_=selector_config.get('class'))
                
                if element:
                    if attribute:
                        text = element.get(attribute)
                    else:
                        text = element.get_text(strip=True)
                    
                    if text:
                        self.logger.debug(f"Extracted with selector: {selector}")
                        return clean_text(text)
                        
            except Exception as e:
                self.logger.debug(f"Selector failed: {selector} - {e}")
                continue
        
        return None
    
    def scrape(self, url: str) -> Optional[Dict[str, Any]]:
        """
        Main scraping method
        
        Args:
            url: Product URL
            
        Returns:
            Dictionary with product info or None
        """
        soup = self.fetch_page(url)
        if not soup:
            return None
        
        result = self.extract_product_info(soup, url)
        
        # Parse price to float
        if result and result.get('price_text'):
            result['price'] = parse_currency(result['price_text'])
        
        return result
    
    @property
    @abstractmethod
    def site_name(self) -> str:
        """Return the site name"""
        pass
    
    @abstractmethod
    def extract_product_info(self, soup: BeautifulSoup, url: str) -> Optional[Dict[str, Any]]:
        """
        Extract product information from page
        
        Args:
            soup: BeautifulSoup object
            url: Original URL
            
        Returns:
            Dictionary with title, price_text, etc.
        """
        pass
