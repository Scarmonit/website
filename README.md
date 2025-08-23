# Parker Directory

A secure file directory browser with authentication and file viewing capabilities.

## Features

- **Secure Authentication**: Password-protected access to all resources
- **File Browser**: Interactive directory listing with search, sort, and filtering
- **File Viewer**: View text-based files with syntax highlighting
- **Responsive Design**: Mobile-friendly interface that works on all devices
- **Drag & Drop**: Organize files by dragging them into folders

## Pages

- **Home** (`index.php`): Directory listing with file management features
- **File Viewer** (`file-viewer.php`): Code/text file viewer with syntax highlighting
- **About Me** (`me.php`): Personal profile page with responsive design

## Security

The application uses cookie-based authentication and prevents directory traversal attacks. All pages require authentication except for the login page.

## Setup

1. Place files in your web server directory
2. Configure the correct passphrase in `login.php`
3. Access via browser (default login code: 44747)

## Technologies

- PHP
- HTML5/CSS3
- JavaScript (ES6+)
- Responsive design with CSS variables
- Mobile-optimized UI components

## Customization

You can customize the color scheme by modifying the CSS variables in each file's stylesheet:

```css
:root {
    --primary: #3a86ff;
    --primary-light: #e9f2ff; 
    --dark: #1a202c;
    /* additional variables */
}
```

## Requirements

- PHP 7.0+
- Modern web browser
- Web server (Apache/Nginx)