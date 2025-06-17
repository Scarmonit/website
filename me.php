<?php
// Check for authentication cookie
if (!isset($_COOKIE['parker_authenticated']) || $_COOKIE['parker_authenticated'] !== 'true') {
    header("Location: /parker/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parker - About Me</title>
    <style>
        /* Mobile-first CSS reset and base styles */
        * {
            box-sizing: border-box;
        }
        
        :root {
            /* CSS custom properties for consistent mobile spacing */
            --mobile-padding: 4vw;
            --desktop-padding: 20px;
            --touch-target: 44px;
            --border-radius: clamp(4px, 1vw, 8px);
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            margin: 0;
            /* Mobile-first padding using viewport units */
            padding: var(--mobile-padding);
            max-width: min(900px, 95vw);
            margin: 0 auto;
            background-color: #f8f9fa;
            color: #212529;
            /* Fluid font size for better mobile readability */
            font-size: clamp(14px, 4vw, 16px);
        }

        .container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            /* Ensure full width on very small screens */
            min-width: 0;
        }
        
        header {
            background-color: #343a40;
            color: white;
            /* Mobile-optimized padding with better touch spacing */
            padding: clamp(12px, 3vw, 20px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: clamp(10px, 3vw, 15px);
            /* Better mobile header stacking */
            flex-direction: column;
        }
        
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: clamp(8px, 2vw, 20px);
            flex-wrap: wrap;
            /* Center navigation on mobile */
            justify-content: center;
            width: 100%;
        }
        
        nav a {
            text-decoration: none;
            color: #adb5bd;
            font-weight: 500;
            /* Enhanced touch targets for mobile */
            padding: clamp(12px, 3vw, 12px) clamp(16px, 4vw, 18px);
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
            /* Minimum touch target size */
            min-height: var(--touch-target);
            min-width: var(--touch-target);
            display: flex;
            align-items: center;
            justify-content: center;
            /* Better text sizing for mobile */
            font-size: clamp(14px, 3.5vw, 16px);
        }
        
        nav a:hover,
        nav a:focus {
            color: white;
            background-color: rgba(255,255,255,0.1);
            outline: 2px solid #007bff;
            outline-offset: 2px;
        }
        
        nav a.active {
            color: white;
            background-color: #007bff;
        }
        
        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            /* Enhanced mobile touch target */
            padding: clamp(12px, 3vw, 12px) clamp(20px, 5vw, 24px);
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: clamp(14px, 3.5vw, 16px);
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s ease;
            /* Ensure proper touch target */
            min-height: var(--touch-target);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logout-btn:hover,
        .logout-btn:focus {
            background-color: #c82333;
            outline: 2px solid #fff;
            outline-offset: 2px;
        }
        
        main {
            /* Mobile-first padding with viewport-based scaling */
            padding: clamp(16px, 5vw, 30px);
        }
        
        h1 {
            color: #343a40;
            margin-top: 0;
            margin-bottom: clamp(15px, 4vw, 25px);
            /* Fluid heading size for better mobile scaling */
            font-size: clamp(1.5rem, 6vw, 2.2rem);
            font-weight: 600;
            /* Better line height for mobile readability */
            line-height: 1.2;
        }
        
        section {
            margin-bottom: clamp(20px, 5vw, 35px);
            /* Mobile-optimized padding */
            padding: clamp(16px, 4vw, 25px);
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            border-left: 4px solid #007bff;
            /* Better mobile shadow */
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        p {
            margin: 0;
            /* Mobile-optimized font size with better line spacing */
            font-size: clamp(1rem, 4.2vw, 1.15rem);
            line-height: 1.7;
        }
        
        /* Enhanced mobile responsiveness with more breakpoints */
        @media (min-width: 480px) {
            /* Small tablets and large phones - minor adjustments */
            section {
                padding: clamp(20px, 4vw, 28px);
            }
        }
        
        @media (min-width: 768px) {
            /* Tablets and small desktops */
            body {
                padding: var(--desktop-padding);
                font-size: 16px;
            }
            
            header {
                flex-direction: row;
                justify-content: space-between;
            }
            
            nav ul {
                width: auto;
                justify-content: flex-start;
            }
            
            main {
                padding: 30px;
            }
            
            section {
                padding: 25px;
            }
        }
        
        @media (min-width: 1024px) {
            /* Large screens - optimal spacing */
            section {
                padding: 30px;
            }
        }
        
        /* Enhanced focus styles for better mobile accessibility */
        *:focus {
            outline: 2px solid #007bff;
            outline-offset: 2px;
        }
        
        /* Better mobile skip link */
        .skip-link {
            position: absolute;
            top: -60px;
            left: clamp(6px, 2vw, 12px);
            background: #007bff;
            color: white;
            padding: clamp(8px, 2vw, 12px) clamp(12px, 3vw, 16px);
            text-decoration: none;
            border-radius: var(--border-radius);
            z-index: 1000;
            font-size: clamp(14px, 3.5vw, 16px);
            min-height: var(--touch-target);
            display: flex;
            align-items: center;
        }
        
        .skip-link:focus {
            top: clamp(6px, 2vw, 12px);
        }
        
        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            /* Increase touch targets on touch devices */
            nav a,
            .logout-btn {
                min-height: 48px;
            }
        }
        
        /* Reduced motion for accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
            }
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <div class="container">
        <header role="banner">
            <nav role="navigation" aria-label="Main navigation">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="file-viewer.php">File Viewer</a></li>
                    <li><a href="me.php" class="active" aria-current="page">About Me</a></li>
                </ul>
            </nav>
            <a href="logout.php" class="logout-btn" aria-label="Logout from Parker Directory">Logout</a>
        </header>
        
        <main id="main-content" role="main">
            <h1>About Me</h1>
            <section>
                <p>Welcome to my personal page! This content is password protected and accessible only to authenticated users.</p>
            </section>
        </main>
    </div>
</body>
</html>