"""
Notifications module for the Personal Price Tracker

This module handles sending notifications when price thresholds are met:
- Email notifications via SMTP
- Desktop notifications using plyer
- Notification templates and formatting
- Rate limiting to prevent spam
"""

import smtplib
from email.mime.text import MimeText
from email.mime.multipart import MimeMultipart
from datetime import datetime
from typing import Dict, Optional
import logging

# Import plyer for desktop notifications (handle missing dependency gracefully)
try:
    from plyer import notification as desktop_notification
    DESKTOP_NOTIFICATIONS_AVAILABLE = True
except ImportError:
    DESKTOP_NOTIFICATIONS_AVAILABLE = False
    # Only show warning when actually needed, not on every import


class NotificationManager:
    """Manages email and desktop notifications for price alerts"""
    
    def __init__(self, email_config: Dict = None, desktop_enabled: bool = True):
        """
        Initialize notification manager
        
        Args:
            email_config: Email configuration dictionary
            desktop_enabled: Whether to enable desktop notifications
        """
        self.email_config = email_config or {}
        self.desktop_enabled = desktop_enabled and DESKTOP_NOTIFICATIONS_AVAILABLE
        
        # Email notification template
        self.email_template = """
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { background-color: white; max-width: 600px; margin: 0 auto; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; color: #2c3e50; margin-bottom: 30px; }
        .price-alert { background-color: #e8f5e8; border: 2px solid #4caf50; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .product-info { background-color: #f8f9fa; border-radius: 5px; padding: 15px; margin: 15px 0; }
        .price { font-size: 24px; font-weight: bold; color: #4caf50; }
        .threshold { font-size: 18px; color: #666; }
        .savings { font-size: 16px; color: #e74c3c; font-weight: bold; }
        .url { word-wrap: break-word; font-size: 14px; color: #3498db; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
        .button { display: inline-block; background-color: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 Price Alert!</h1>
            <p>Great news! A product you're tracking has dropped in price!</p>
        </div>
        
        <div class="price-alert">
            <h2>💰 Price Drop Detected</h2>
            
            <div class="product-info">
                <h3>{product_name}</h3>
                <div class="price">Current Price: ${current_price:.2f}</div>
                <div class="threshold">Your Threshold: ${threshold:.2f}</div>
                {savings_html}
                <div class="url">
                    <strong>Product URL:</strong><br>
                    <a href="{product_url}" class="button">View Product</a>
                </div>
            </div>
        </div>
        
        <div class="product-info">
            <h3>📊 Recent Price History</h3>
            {price_history_html}
        </div>
        
        <div class="footer">
            <p>This alert was sent by your Personal Price Tracker on {timestamp}</p>
            <p>Happy shopping! 🛍️</p>
        </div>
    </div>
</body>
</html>
        """
    
    def format_price_history(self, price_history: list, limit: int = 5) -> str:
        """
        Format price history for email display
        
        Args:
            price_history: List of price history records
            limit: Maximum number of records to show
            
        Returns:
            HTML formatted price history
        """
        if not price_history:
            return "<p>No price history available.</p>"
        
        html = "<ul>"
        for record in price_history[:limit]:
            if record.get('status') == 'success' and record.get('price'):
                date = record['checked_at'][:10] if record.get('checked_at') else 'Unknown'
                price = record['price']
                html += f"<li>{date}: ${price:.2f}</li>"
        html += "</ul>"
        
        return html
    
    def send_email_notification(self, product_data: Dict, price_history: list = None) -> bool:
        """
        Send email notification for price drop
        
        Args:
            product_data: Dictionary containing product information
            price_history: Recent price history for the product
            
        Returns:
            True if email was sent successfully
        """
        if not self.email_config.get('enabled', False):
            print("📧 Email notifications are disabled")
            return False
        
        try:
            # Extract configuration
            smtp_server = self.email_config['smtp_server']
            smtp_port = self.email_config['smtp_port']
            sender_email = self.email_config['sender_email']
            sender_password = self.email_config['sender_password']
            recipient_email = self.email_config['recipient_email']
            
            # Create message
            msg = MimeMultipart('alternative')
            msg['From'] = sender_email
            msg['To'] = recipient_email
            msg['Subject'] = f"🎯 Price Drop Alert: {product_data['name']}"
            
            # Calculate savings if we have previous price
            savings_html = ""
            if price_history and len(price_history) > 1:
                previous_price = None
                for record in price_history:
                    if record.get('status') == 'success' and record.get('price'):
                        if record['price'] > product_data['current_price']:
                            previous_price = record['price']
                            break
                
                if previous_price:
                    savings = previous_price - product_data['current_price']
                    savings_percent = (savings / previous_price) * 100
                    savings_html = f'<div class="savings">💸 You save: ${savings:.2f} ({savings_percent:.1f}% off!)</div>'
            
            # Format price history
            price_history_html = self.format_price_history(price_history or [])
            
            # Create HTML content
            html_content = self.email_template.format(
                product_name=product_data['name'],
                current_price=product_data['current_price'],
                threshold=product_data['threshold'],
                savings_html=savings_html,
                product_url=product_data['url'],
                price_history_html=price_history_html,
                timestamp=datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            )
            
            # Create plain text version
            text_content = f"""
🎯 PRICE ALERT!

Product: {product_data['name']}
Current Price: ${product_data['current_price']:.2f}
Your Threshold: ${product_data['threshold']:.2f}

{savings_html.replace('<div class="savings">', '').replace('</div>', '') if savings_html else ''}

Product URL: {product_data['url']}

This alert was sent on {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}

Happy shopping! 🛍️
            """
            
            # Attach parts
            msg.attach(MimeText(text_content, 'plain'))
            msg.attach(MimeText(html_content, 'html'))
            
            # Send email with timeout for better error handling
            print(f"📧 Sending email notification to {recipient_email}...")
            
            with smtplib.SMTP(smtp_server, smtp_port, timeout=30) as server:  # Add timeout
                server.starttls()
                server.login(sender_email, sender_password)
                server.send_message(msg)
            
            print("✅ Email notification sent successfully!")
            return True
            
        except Exception as e:
            print(f"❌ Failed to send email notification: {e}")
            return False
    
    def send_desktop_notification(self, product_data: Dict) -> bool:
        """
        Send desktop notification for price drop
        
        Args:
            product_data: Dictionary containing product information
            
        Returns:
            True if notification was sent successfully
        """
        if not self.desktop_enabled:
            # Show warning only when desktop notifications are actually needed
            if not DESKTOP_NOTIFICATIONS_AVAILABLE:
                print("🖥️  Desktop notifications not available. Install with: pip install plyer")
            else:
                print("🖥️  Desktop notifications are disabled in configuration")
            return False
        
        try:
            title = "🎯 Price Drop Alert!"
            message = (
                f"{product_data['name']}\n"
                f"💰 Now: ${product_data['current_price']:.2f}\n"
                f"🎯 Threshold: ${product_data['threshold']:.2f}\n"
                f"Click to view product!"
            )
            
            desktop_notification.notify(
                title=title,
                message=message,
                timeout=10,  # Show for 10 seconds
                app_name="Price Tracker",
                app_icon=None  # Use default icon
            )
            
            print("✅ Desktop notification sent successfully!")
            return True
            
        except Exception as e:
            print(f"❌ Failed to send desktop notification: {e}")
            return False
    
    def send_price_alert(self, product_data: Dict, price_history: list = None) -> Dict[str, bool]:
        """
        Send both email and desktop notifications
        
        Args:
            product_data: Dictionary containing product information
            price_history: Recent price history for the product
            
        Returns:
            Dictionary with success status for each notification type
        """
        results = {
            'email': False,
            'desktop': False
        }
        
        print(f"🚨 Sending price alert for: {product_data['name']}")
        print(f"   Current price: ${product_data['current_price']:.2f}")
        print(f"   Threshold: ${product_data['threshold']:.2f}")
        
        # Send email notification
        if self.email_config.get('enabled', False):
            results['email'] = self.send_email_notification(product_data, price_history)
        
        # Send desktop notification
        if self.desktop_enabled:
            results['desktop'] = self.send_desktop_notification(product_data)
        
        return results
    
    def test_email_config(self) -> bool:
        """
        Test email configuration by sending a test email
        
        Returns:
            True if test email was sent successfully
        """
        if not self.email_config.get('enabled', False):
            print("📧 Email notifications are disabled")
            return False
        
        test_product = {
            'name': 'Test Product',
            'current_price': 19.99,
            'threshold': 25.00,
            'url': 'https://example.com/test'
        }
        
        print("📧 Sending test email...")
        return self.send_email_notification(test_product)
    
    def test_desktop_notification(self) -> bool:
        """
        Test desktop notification functionality
        
        Returns:
            True if test notification was sent successfully
        """
        test_product = {
            'name': 'Test Product',
            'current_price': 19.99,
            'threshold': 25.00,
            'url': 'https://example.com/test'
        }
        
        print("🖥️  Sending test desktop notification...")
        return self.send_desktop_notification(test_product)


if __name__ == "__main__":
    # Test notifications functionality
    print("🧪 Testing notifications system...")
    
    # Example email configuration (don't use real credentials here)
    email_config = {
        'enabled': False,  # Set to True and add real credentials to test
        'smtp_server': 'smtp.gmail.com',
        'smtp_port': 587,
        'sender_email': 'your-email@gmail.com',
        'sender_password': 'your-app-password',
        'recipient_email': 'your-email@gmail.com'
    }
    
    # Initialize notification manager
    notifier = NotificationManager(email_config=email_config, desktop_enabled=True)
    
    # Test data
    test_product = {
        'name': 'Test Gaming Headset',
        'current_price': 45.99,
        'threshold': 50.00,
        'url': 'https://example.com/gaming-headset'
    }
    
    test_price_history = [
        {'price': 59.99, 'checked_at': '2024-01-15 10:00:00', 'status': 'success'},
        {'price': 54.99, 'checked_at': '2024-01-16 10:00:00', 'status': 'success'},
        {'price': 45.99, 'checked_at': '2024-01-17 10:00:00', 'status': 'success'},
    ]
    
    # Test desktop notification
    if DESKTOP_NOTIFICATIONS_AVAILABLE:
        print("\n🖥️  Testing desktop notification...")
        notifier.test_desktop_notification()
    else:
        print("\n⚠️  Desktop notifications not available")
    
    # Test email (only if enabled)
    if email_config.get('enabled'):
        print("\n📧 Testing email notification...")
        notifier.test_email_config()
    else:
        print("\n📧 Email notifications disabled (configure in email_config)")
    
    print("\n🧪 Notification test completed!")
    print("💡 To enable email notifications:")
    print("   1. Update email_config with your credentials")
    print("   2. Set 'enabled': True")
    print("   3. For Gmail, use an app password (not your regular password)")
