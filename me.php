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
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            max-width: 900px;
            margin: 0 auto;
            background-color: #f8f9fa;
            color: #212529;
        }

        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        header {
            background-color: #343a40;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        nav a {
            text-decoration: none;
            color: #adb5bd;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s ease;
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
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        
        .logout-btn:hover,
        .logout-btn:focus {
            background-color: #c82333;
            outline: 2px solid #fff;
            outline-offset: 2px;
        }
        
        main {
            padding: 30px;
        }
        
        h1 {
            color: #343a40;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 2rem;
            font-weight: 600;
        }
        
        section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #007bff;
        }
        
        p {
            margin: 0;
            font-size: 1.1rem;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            nav ul {
                justify-content: center;
            }
            
            main {
                padding: 20px;
            }
            
            h1 {
                font-size: 1.75rem;
            }
            
            section {
                padding: 15px;
            }
        }
        
        @media (max-width: 480px) {
            nav ul {
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }
            
            nav a {
                text-align: center;
                width: 100%;
            }
        }
        
        /* Focus styles for accessibility */
        *:focus {
            outline: 2px solid #007bff;
            outline-offset: 2px;
        }
        
        /* Skip link for accessibility */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: #007bff;
            color: white;
            padding: 8px;
            text-decoration: none;
            border-radius: 4px;
            z-index: 1000;
        }
        
        .skip-link:focus {
            top: 6px;
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