"""
Notification service for price alerts
"""

import smtplib
from email.mime.text import MimeText
from email.mime.multipart import MimeMultipart
from typing import Optional, Dict, Any
from configparser import ConfigParser
from datetime import datetime

from db.models import Product, Notification
from services.logger import setup_logger


class NotificationService:
    """Service for sending price drop notifications"""
    
    def __init__(self):
        """Initialize notification service"""
        self.logger = setup_logger(self.__class__.__name__)
        self.config = ConfigParser()
        self.config.read('config.ini')
        
        # Check if desktop notifications are available
        self.desktop_available = False
        if self.config.getboolean('notifications', 'desktop_enabled', fallback=False):
            try:
                from plyer import notification
                self.desktop_available = True
                self.desktop_notify = notification
            except ImportError:
                self.logger.warning("Desktop notifications not available. Install plyer: pip install plyer")
    
    def send_email(self, product: Product, old_price: float, new_price: float) -> bool:
        """
        Send email notification for price drop
        
        Args:
            product: Product with price drop
            old_price: Previous price
            new_price: Current price
            
        Returns:
            True if email sent successfully
        """
        try:
            # Get email configuration
            smtp_server = self.config.get('email', 'smtp_server')
            smtp_port = self.config.getint('email', 'smtp_port')
            sender_email = self.config.get('email', 'sender_email')
            sender_password = self.config.get('email', 'sender_password')
            recipient_email = self.config.get('email', 'recipient_email')
            
            # Calculate savings
            savings = old_price - new_price
            savings_percent = (savings / old_price) * 100
            
            # Create message
            msg = MimeMultipart('alternative')
            msg['From'] = sender_email
            msg['To'] = recipient_email
            msg['Subject'] = f"🎯 Price Drop Alert: {product.name[:50]}"
            
            # Create HTML content
            html = f"""
            <html>
            <head>
                <style>
                    body {{ font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }}
                    .container {{ background: white; max-width: 600px; margin: 0 auto; padding: 20px; border-radius: 10px; }}
                    .header {{ background: #2ecc71; color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; }}
                    .product {{ padding: 20px; background: #ecf0f1; margin: 20px 0; border-radius: 5px; }}
                    .price-box {{ display: inline-block; padding: 10px 20px; margin: 10px; border-radius: 5px; }}
                    .old-price {{ background: #e74c3c; color: white; text-decoration: line-through; }}
                    .new-price {{ background: #27ae60; color: white; font-size: 1.2em; font-weight: bold; }}
                    .savings {{ background: #f39c12; color: white; padding: 15px; margin: 20px 0; border-radius: 5px; text-align: center; }}
                    .button {{ display: inline-block; background: #3498db; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }}
                    .footer {{ text-align: center; color: #666; font-size: 12px; margin-top: 30px; }}
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>🎯 Price Drop Alert!</h1>
                    </div>
                    
                    <div class="product">
                        <h2>{product.name}</h2>
                        <p><strong>Site:</strong> {product.site}</p>
                        <p><strong>Target Price:</strong> ${product.target_price:.2f}</p>
                        
                        <div style="text-align: center; margin: 20px 0;">
                            <span class="price-box old-price">Was: ${old_price:.2f}</span>
                            <span style="font-size: 2em;">→</span>
                            <span class="price-box new-price">Now: ${new_price:.2f}</span>
                        </div>
                        
                        <div class="savings">
                            <h3>You Save: ${savings:.2f} ({savings_percent:.1f}% OFF!)</h3>
                        </div>
                        
                        <div style="text-align: center;">
                            <a href="{product.url}" class="button">View Product</a>
                        </div>
                    </div>
                    
                    <div class="footer">
                        <p>Price checked at {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}</p>
                        <p>This is an automated alert from your Personal Price Tracker</p>
                    </div>
                </div>
            </body>
            </html>
            """
            
            # Create plain text version
            text = f"""
            PRICE DROP ALERT!
            
            Product: {product.name}
            Site: {product.site}
            
            Old Price: ${old_price:.2f}
            NEW PRICE: ${new_price:.2f}
            Target Price: ${product.target_price:.2f}
            
            YOU SAVE: ${savings:.2f} ({savings_percent:.1f}% OFF!)
            
            View Product: {product.url}
            
            Price checked at {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}
            """
            
            # Attach parts
            msg.attach(MimeText(text, 'plain'))
            msg.attach(MimeText(html, 'html'))
            
            # Send email
            with smtplib.SMTP(smtp_server, smtp_port, timeout=30) as server:
                server.starttls()
                server.login(sender_email, sender_password)
                server.send_message(msg)
            
            self.logger.info(f"Email sent for product: {product.name}")
            return True
            
        except Exception as e:
            self.logger.error(f"Failed to send email: {e}")
            return False
    
    def send_desktop(self, product: Product, old_price: float, new_price: float) -> bool:
        """
        Send desktop notification for price drop
        
        Args:
            product: Product with price drop
            old_price: Previous price
            new_price: Current price
            
        Returns:
            True if notification sent successfully
        """
        if not self.desktop_available:
            return False
        
        try:
            savings = old_price - new_price
            
            title = f"Price Drop: {product.name[:30]}"
            message = (
                f"${old_price:.2f} → ${new_price:.2f}\n"
                f"Save ${savings:.2f}!\n"
                f"Target: ${product.target_price:.2f}"
            )
            
            self.desktop_notify.notify(
                title=title,
                message=message,
                timeout=10,
                app_name="Price Tracker"
            )
            
            self.logger.info(f"Desktop notification sent for: {product.name}")
            return True
            
        except Exception as e:
            self.logger.error(f"Failed to send desktop notification: {e}")
            return False
    
    def notify_price_drop(self, product: Product, old_price: float, new_price: float,
                         db=None) -> Dict[str, bool]:
        """
        Send all configured notifications for a price drop
        
        Args:
            product: Product with price drop
            old_price: Previous price
            new_price: Current price
            db: Database connection (optional)
            
        Returns:
            Dictionary with notification results
        """
        results = {
            'email': False,
            'desktop': False
        }
        
        # Send email notification
        if self.config.get('email', 'sender_email', fallback=None):
            results['email'] = self.send_email(product, old_price, new_price)
            
            # Log notification to database
            if db and results['email']:
                notification = Notification(
                    product_id=product.id,
                    type='email',
                    sent_at=datetime.now(),
                    price_at_notification=new_price,
                    message=f"Price dropped from ${old_price:.2f} to ${new_price:.2f}",
                    success=True
                )
                db.add_notification(notification)
        
        # Send desktop notification
        if self.desktop_available:
            results['desktop'] = self.send_desktop(product, old_price, new_price)
            
            # Log notification to database
            if db and results['desktop']:
                notification = Notification(
                    product_id=product.id,
                    type='desktop',
                    sent_at=datetime.now(),
                    price_at_notification=new_price,
                    message=f"Price dropped from ${old_price:.2f} to ${new_price:.2f}",
                    success=True
                )
                db.add_notification(notification)
        
        return results
