"""
Tests for parsing utilities
"""

import pytest
from utils.parsing import parse_currency, clean_text, extract_number, normalize_url


class TestParseCurrency:
    """Test currency parsing"""
    
    def test_basic_prices(self):
        """Test basic price formats"""
        assert parse_currency("$19.99") == 19.99
        assert parse_currency("$1,234.56") == 1234.56
        assert parse_currency("99.99") == 99.99
        assert parse_currency("1234") == 1234.0
    
    def test_european_format(self):
        """Test European price formats"""
        assert parse_currency("€99,99") == 99.99
        assert parse_currency("1.234,56") == 1234.56
    
    def test_with_text(self):
        """Test prices with surrounding text"""
        assert parse_currency("Price: $49.99") == 49.99
        assert parse_currency("Was $99.99 Now $79.99") == 99.99  # Takes first price
        assert parse_currency("USD 123.45") == 123.45
    
    def test_ranges(self):
        """Test price ranges"""
        assert parse_currency("$10.99 - $19.99") == 10.99
        assert parse_currency("$5 to $10") == 5.0
    
    def test_edge_cases(self):
        """Test edge cases"""
        assert parse_currency("") is None
        assert parse_currency(None) is None
        assert parse_currency("No price") is None
        assert parse_currency("$0.00") is None  # Zero price
        assert parse_currency("$9999999") is None  # Too high


class TestCleanText:
    """Test text cleaning"""
    
    def test_basic_cleaning(self):
        """Test basic text cleaning"""
        assert clean_text("  Hello World  ") == "Hello World"
        assert clean_text("Hello\n\nWorld") == "Hello World"
        assert clean_text("Hello\t\tWorld") == "Hello World"
    
    def test_zero_width_chars(self):
        """Test removal of zero-width characters"""
        assert clean_text("Hello\u200bWorld") == "HelloWorld"
        assert clean_text("Test\ufeffString") == "TestString"
    
    def test_edge_cases(self):
        """Test edge cases"""
        assert clean_text("") == ""
        assert clean_text(None) == ""
        assert clean_text(123) == "123"


class TestExtractNumber:
    """Test number extraction"""
    
    def test_basic_extraction(self):
        """Test basic number extraction"""
        assert extract_number("123") == 123.0
        assert extract_number("45.67") == 45.67
        assert extract_number("Item #123") == 123.0
    
    def test_multiple_numbers(self):
        """Test extraction with multiple numbers"""
        assert extract_number("Was 99.99 Now 79.99") == 99.99  # First number
    
    def test_edge_cases(self):
        """Test edge cases"""
        assert extract_number("") is None
        assert extract_number(None) is None
        assert extract_number("No numbers here") is None


class TestNormalizeUrl:
    """Test URL normalization"""
    
    def test_remove_tracking_params(self):
        """Test removal of tracking parameters"""
        url = "https://example.com/product?id=123&utm_source=email&utm_campaign=sale"
        expected = "https://example.com/product?id=123"
        assert normalize_url(url) == expected
    
    def test_preserve_important_params(self):
        """Test preservation of important parameters"""
        url = "https://example.com/product?id=123&color=blue&size=large"
        assert normalize_url(url) == url
    
    def test_remove_fragment(self):
        """Test removal of URL fragment"""
        url = "https://example.com/product#reviews"
        expected = "https://example.com/product"
        assert normalize_url(url) == expected
    
    def test_edge_cases(self):
        """Test edge cases"""
        assert normalize_url("") == ""
        assert normalize_url(None) == ""
