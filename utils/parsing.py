"""
Parsing utilities for price extraction and text cleaning
"""

import re
from typing import Optional, Union


def parse_currency(price_text: str) -> Optional[float]:
    """
    Parse currency string to float
    
    Args:
        price_text: Text containing price (e.g., "$1,234.56", "€99,99")
        
    Returns:
        Parsed price as float or None
    """
    if not price_text:
        return None
    
    # Remove whitespace
    price_text = str(price_text).strip()
    
    # Handle ranges (take the lower price)
    if ' - ' in price_text or ' to ' in price_text.lower():
        parts = re.split(r'\s*[-–]\s*|\s+to\s+', price_text.lower())
        if parts:
            price_text = parts[0]
    
    # Remove currency symbols and text
    price_text = re.sub(r'[^\d.,\s-]', '', price_text)
    
    # Handle different decimal separators
    # Check if comma is decimal separator (European format)
    if ',' in price_text and '.' not in price_text:
        # Only comma present, could be decimal separator
        if price_text.count(',') == 1:
            parts = price_text.split(',')
            if len(parts) == 2 and len(parts[1]) <= 2:
                # Likely European format (e.g., "99,99")
                price_text = price_text.replace(',', '.')
            else:
                # Likely thousands separator (e.g., "1,234")
                price_text = price_text.replace(',', '')
        else:
            # Multiple commas, must be thousands separator
            price_text = price_text.replace(',', '')
    elif ',' in price_text and '.' in price_text:
        # Both present - need to determine which is decimal separator
        comma_pos = price_text.rfind(',')
        dot_pos = price_text.rfind('.')
        
        # If comma is after dot and close to end, it's likely European format
        if comma_pos > dot_pos and len(price_text) - comma_pos <= 3:
            # European format: "1.234,56" -> thousands=dot, decimal=comma
            price_text = price_text.replace('.', '').replace(',', '.')
        else:
            # US format: "1,234.56" -> thousands=comma, decimal=dot
            price_text = price_text.replace(',', '')
    elif ',' in price_text:
        # Comma as thousands separator
        price_text = price_text.replace(',', '')
    
    # Extract number
    match = re.search(r'[\d]+\.?[\d]*', price_text)
    if match:
        try:
            price = float(match.group())
            # Sanity check - prices should be positive and reasonable
            if 0 < price < 1000000:
                return price
        except ValueError:
            pass
    
    return None


def clean_text(text: str) -> str:
    """
    Clean and normalize text
    
    Args:
        text: Raw text to clean
        
    Returns:
        Cleaned text
    """
    if not text:
        return ""
    
    # Convert to string and strip whitespace
    text = str(text).strip()
    
    # Remove excessive whitespace
    text = re.sub(r'\s+', ' ', text)
    
    # Remove zero-width characters
    text = re.sub(r'[\u200b\u200c\u200d\ufeff]', '', text)
    
    return text


def extract_number(text: str) -> Optional[float]:
    """
    Extract first number from text
    
    Args:
        text: Text containing number
        
    Returns:
        Extracted number or None
    """
    if not text:
        return None
    
    # Find all numbers (including decimals)
    matches = re.findall(r'[\d]+\.?[\d]*', str(text))
    
    if matches:
        try:
            return float(matches[0])
        except ValueError:
            pass
    
    return None


def normalize_url(url: str) -> str:
    """
    Normalize product URL (remove tracking parameters, etc.)
    
    Args:
        url: Product URL
        
    Returns:
        Normalized URL
    """
    if not url:
        return ""
    
    # Remove common tracking parameters
    tracking_params = [
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
        'ref', 'ref_', 'tag', 'linkCode', 'camp', 'creative', 'creativeASIN',
        'fbclid', 'gclid', 'msclkid'
    ]
    
    # Parse URL
    from urllib.parse import urlparse, parse_qs, urlencode, urlunparse
    
    parsed = urlparse(url)
    params = parse_qs(parsed.query)
    
    # Remove tracking parameters
    filtered_params = {
        k: v for k, v in params.items() 
        if k not in tracking_params
    }
    
    # Rebuild URL
    new_query = urlencode(filtered_params, doseq=True)
    normalized = urlunparse((
        parsed.scheme,
        parsed.netloc,
        parsed.path,
        parsed.params,
        new_query,
        ''  # Remove fragment
    ))
    
    return normalized
