"""
Sample HTML pages for testing scrapers
"""

AMAZON_PRODUCT_HTML = """
<html>
<head><title>Test Product - Amazon</title></head>
<body>
    <span id="productTitle">Apple AirPods Pro (2nd Generation)</span>
    <div class="a-section">
        <span class="a-price-whole">249</span>
        <span class="a-price-fraction">99</span>
    </div>
    <div id="availability">
        <span class="a-size-medium a-color-success">In Stock</span>
    </div>
    <a id="bylineInfo">Visit the Apple Store</a>
</body>
</html>
"""

EBAY_PRODUCT_HTML = """
<html>
<head><title>Test Product - eBay</title></head>
<body>
    <h1 class="it-ttl">Samsung Galaxy S23 Ultra - 256GB - Phantom Black</h1>
    <div class="u-flL">
        <span class="notranslate">$899.99</span>
    </div>
    <div class="u-flL condText">New</div>
    <span class="mbg-nw">techdeals_store</span>
    <span class="vi-ship-free">Free shipping</span>
</body>
</html>
"""

WALMART_PRODUCT_HTML = """
<html>
<head><title>Test Product - Walmart</title></head>
<body>
    <h1 itemprop="name">Sony PlayStation 5 Console</h1>
    <span itemprop="price">499.99</span>
    <div data-testid="fulfillment-badge">
        <span>In stock</span>
    </div>
    <div class="sold-shipped-by">
        <a data-testid="seller-name">Walmart.com</a>
    </div>
</body>
</html>
"""
