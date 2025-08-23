"""
Price tracker implementations for various e-commerce sites
"""

from typing import Optional
from urllib.parse import urlparse

from .base import TrackerBase
from .amazon import AmazonTracker
from .ebay import EbayTracker
from .walmart import WalmartTracker


def get_tracker(url: str) -> Optional[TrackerBase]:
    """
    Factory function to get appropriate tracker for a URL
    
    Args:
        url: Product URL
        
    Returns:
        Appropriate tracker instance or None if unsupported
    """
    domain = urlparse(url).netloc.lower()
    
    if 'amazon' in domain:
        return AmazonTracker()
    elif 'ebay' in domain:
        return EbayTracker()
    elif 'walmart' in domain:
        return WalmartTracker()
    
    return None


__all__ = ['get_tracker', 'TrackerBase', 'AmazonTracker', 'EbayTracker', 'WalmartTracker']
