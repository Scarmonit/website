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
            /* Enhanced color scheme with CSS variables */
            --primary: #3a86ff;
            --primary-light: #e9f2ff;
            --primary-dark: #2563eb;
            --secondary: #38b2ac;
            --dark: #1a202c;
            --dark-accent: #2d3748;
            --light: #f8f9fa;
            --light-accent: #e2e8f0;
            --success: #48bb78;
            --warning: #f59e0b;
            --danger: #e53e3e;
            --text-dark: #1a202c;
            --text-medium: #4a5568;
            --text-light: #a0aec0;
            
            /* Spacing system */
            --mobile-padding: 4vw;
            --desktop-padding: 20px;
            --touch-target: 44px;
            --border-radius: clamp(4px, 1vw, 8px);
            --card-radius: clamp(6px, 1.25vw, 10px);
            
            /* Shadows */
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1), 0 2px 4px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1), 0 4px 6px rgba(0,0,0,0.05);
            
            /* Animation speeds */
            --transition-fast: 150ms;
            --transition-normal: 250ms;
            --transition-slow: 350ms;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: var(--mobile-padding);
            max-width: min(1200px, 95vw);
            margin: 0 auto;
            background-color: var(--light);
            color: var(--text-dark);
            font-size: clamp(14px, 4vw, 16px);
        }

        .container {
            background-color: white;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            min-width: 0;
            transition: box-shadow var(--transition-normal) ease;
        }
        
        .container:hover {
            box-shadow: var(--shadow-lg);
        }
        
        header {
            background-color: var(--dark);
            color: white;
            padding: clamp(12px, 3vw, 20px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: clamp(10px, 3vw, 15px);
            flex-direction: column;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: clamp(8px, 2vw, 20px);
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
        }
        
        nav a {
            text-decoration: none;
            color: var(--text-light);
            font-weight: 500;
            padding: clamp(12px, 3vw, 12px) clamp(16px, 4vw, 18px);
            border-radius: var(--border-radius);
            transition: all var(--transition-normal) ease;
            min-height: var(--touch-target);
            min-width: var(--touch-target);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(14px, 3.5vw, 16px);
            position: relative;
        }
        
        nav a:hover,
        nav a:focus {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        
        /* Animated underline for nav links */
        nav a::after {
            content: '';
            position: absolute;
            bottom: 8px;
            left: 50%;
            width: 0;
            height: 2px;
            background-color: var(--primary);
            transition: all var(--transition-normal) ease;
            transform: translateX(-50%);
            opacity: 0;
        }
        
        nav a:hover::after,
        nav a:focus::after {
            width: 60%;
            opacity: 1;
        }
        
        nav a.active {
            color: white;
            background-color: var(--primary);
        }
        
        nav a.active::after {
            width: 70%;
            opacity: 1;
            background-color: white;
        }
        
        .logout-btn {
            background-color: var(--danger);
            color: white;
            border: none;
            padding: clamp(12px, 3vw, 12px) clamp(20px, 5vw, 24px);
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: clamp(14px, 3.5vw, 16px);
            text-decoration: none;
            font-weight: 500;
            transition: all var(--transition-normal) ease;
            min-height: var(--touch-target);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .logout-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background-color: rgba(0,0,0,0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width var(--transition-normal) ease, height var(--transition-normal) ease;
        }
        
        .logout-btn:hover::before,
        .logout-btn:focus::before {
            width: 300%;
            height: 300%;
        }
        
        .logout-btn:hover,
        .logout-btn:focus {
            background-color: #c82333;
        }
        
        main {
            padding: clamp(16px, 5vw, 30px);
        }
        
        h1 {
            color: var(--dark);
            margin-top: 0;
            margin-bottom: clamp(12px, 3vw, 20px);
            font-size: clamp(1.5rem, 6vw, 2.2rem);
            font-weight: 600;
            line-height: 1.2;
            position: relative;
            display: inline-block;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: var(--primary);
            border-radius: 3px;
        }
        
        /* Enhanced about me sections */
        .profile-card {
            display: flex;
            flex-direction: column;
            gap: clamp(20px, 5vw, 30px);
            margin-bottom: clamp(25px, 6vw, 40px);
            background-color: white;
            border-radius: var(--card-radius);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            animation: fadeIn var(--transition-slow) ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .profile-header {
            background-color: var(--primary-light);
            padding: clamp(20px, 5vw, 30px);
            border-bottom: 1px solid var(--light-accent);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 15px;
        }
        
        .profile-image {
            width: clamp(100px, 25vw, 150px);
            height: clamp(100px, 25vw, 150px);
            border-radius: 50%;
            background-color: var(--light-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(2rem, 8vw, 3rem);
            color: var(--primary);
            overflow: hidden;
            border: 4px solid white;
            box-shadow: var(--shadow-sm);
            transition: transform var(--transition-normal) ease;
        }
        
        .profile-image:hover {
            transform: scale(1.05);
        }
        
        .profile-name {
            font-size: clamp(1.5rem, 5vw, 2rem);
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }
        
        .profile-title {
            font-size: clamp(1rem, 3.5vw, 1.2rem);
            color: var(--text-medium);
            margin: 0;
        }
        
        .profile-content {
            padding: clamp(20px, 5vw, 30px);
        }
        
        .profile-section {
            margin-bottom: clamp(20px, 5vw, 30px);
        }
        
        .profile-section:last-child {
            margin-bottom: 0;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: clamp(1.2rem, 4vw, 1.4rem);
            font-weight: 600;
            color: var(--dark);
            margin-bottom: clamp(10px, 3vw, 15px);
            padding-bottom: 8px;
            border-bottom: 2px solid var(--light-accent);
        }
        
        .section-title svg {
            color: var(--primary);
        }
        
        .section-content {
            font-size: clamp(1rem, 3.8vw, 1.1rem);
            line-height: 1.7;
            color: var(--text-medium);
        }
        
        .section-content p:first-child {
            margin-top: 0;
        }
        
        .section-content p:last-child {
            margin-bottom: 0;
        }
        
        .skill-list {
            display: flex;
            flex-wrap: wrap;
            gap: clamp(8px, 2vw, 12px);
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .skill-item {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: clamp(0.9rem, 3vw, 1rem);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all var(--transition-normal) ease;
        }
        
        .skill-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        
        .contact-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: clamp(10px, 3vw, 15px);
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 15px;
            background-color: var(--light);
            border-radius: var(--border-radius);
            transition: all var(--transition-normal) ease;
        }
        
        .contact-item:hover {
            transform: translateX(5px);
            background-color: var(--primary-light);
        }
        
        .contact-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background-color: var(--primary-light);
            color: var(--primary);
            border-radius: 50%;
            font-size: 18px;
        }
        
        .contact-item:hover .contact-icon {
            background-color: var(--primary);
            color: white;
        }
        
        .contact-info {
            flex: 1;
        }
        
        .contact-label {
            font-size: clamp(0.9rem, 3vw, 1rem);
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
        }
        
        .contact-value {
            font-size: clamp(0.85rem, 2.8vw, 0.95rem);
            color: var(--text-medium);
            margin: 0;
        }
        
        /* Enhanced mobile responsiveness */
        @media (min-width: 768px) {
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
            
            .profile-header {
                flex-direction: row;
                text-align: left;
                padding: clamp(25px, 6vw, 35px);
            }
            
            .profile-header-info {
                align-items: flex-start;
            }
            
            .contact-list {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
        
        /* Enhanced focus styles for accessibility */
        *:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }
        
        /* Skip link for accessibility */
        .skip-link {
            position: absolute;
            top: -60px;
            left: clamp(6px, 2vw, 12px);
            background: var(--primary);
            color: white;
            padding: clamp(8px, 2vw, 12px) clamp(12px, 3vw, 16px);
            text-decoration: none;
            border-radius: var(--border-radius);
            z-index: 1000;
            font-size: clamp(14px, 3.5vw, 16px);
            min-height: var(--touch-target);
            display: flex;
            align-items: center;
            transition: top var(--transition-normal) ease;
        }
        
        .skip-link:focus {
            top: clamp(6px, 2vw, 12px);
        }
        
        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            nav a,
            .logout-btn {
                min-height: 48px;
            }
        }
        
        /* Reduced motion for accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
                animation: none !important;
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
            
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-image">
                        <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c0-.001 0-.002 0-.004H3c0 .002 0 .003 0 .004 0 .096.046.19.133.25.117.084.29.158.484.21.289.097.6.15.9.15s.611-.053.9-.15c.194-.052.367-.126.484-.21.087-.06.133-.154.133-.25z"/>
                        </svg>
                    </div>
                    <div class="profile-header-info">
                        <h2 class="profile-name">Parker</h2>
                        <p class="profile-title">Web Developer & Directory Administrator</p>
                    </div>
                </div>
                
                <div class="profile-content">
                    <section class="profile-section">
                        <h3 class="section-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9V5.5z"/>
                                <path d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1h-3zm1.038 3.018a6.093 6.093 0 0 1 .924 0 6 6 0 1 1-.924 0zM0 8.5A.5.5 0 0 1 .5 8h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zM8 3a.5.5 0 0 1 .5.5v3.25a.75.75 0 1 0 1.5 0V3.5a.5.5 0 0 1 1 0v3.25a2.25 2.25 0 1 1-4.5 0V3.5a.5.5 0 0 1 .5-.5z"/>
                            </svg>
                            About
                        </h3>
                        <div class="section-content">
                            <p>Welcome to my personal page! This content is password protected and accessible only to authenticated users.</p>
                            <p>I'm a web developer and directory administrator specializing in creating secure, efficient file systems and web applications.</p>
                        </div>
                    </section>
                    
                    <section class="profile-section">
                        <h3 class="section-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M5 3a3 3 0 0 1 6 0v5a3 3 0 0 1-6 0V3z"/>
                                <path d="M3.5 6.5A.5.5 0 0 1 4 7v1a4 4 0 0 0 8 0V7a.5.5 0 0 1 1 0v1a5 5 0 0 1-4.5 4.975V15h3a.5.5 0 0 1 0 1h-7a.5.5 0 0 1 0-1h3v-2.025A5 5 0 0 1 3 8V7a.5.5 0 0 1 .5-.5z"/>
                            </svg>
                            Skills
                        </h3>
                        <div class="section-content">
                            <ul class="skill-list">
                                <li class="skill-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M10.478 1.647a.5.5 0 1 0-.956-.294l-4 13a.5.5 0 0 0 .956.294l4-13zM4.854 4.146a.5.5 0 0 1 0 .708L1.707 8l3.147 3.146a.5.5 0 0 1-.708.708l-3.5-3.5a.5.5 0 0 1 0-.708l3.5-3.5a.5.5 0 0 1 .708 0zm6.292 0a.5.5 0 0 0 0 .708L14.293 8l-3.147 3.146a.5.5 0 0 0 .708.708l3.5-3.5a.5.5 0 0 0 0-.708l-3.5-3.5a.5.5 0 0 0-.708 0z"/>
                                    </svg>
                                    HTML/CSS
                                </li>
                                <li class="skill-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M9.465 10.045a.5.5 0 0 1-.707 0l-.646-.647-.646.647a.5.5 0 0 1-.707-.708l.646-.646-.646-.646a.5.5 0 0 1 .708-.708l.646.647.646-.647a.5.5 0 0 1 .708.708l-.647.646.647.646a.5.5 0 0 1 0 .708z"/>
                                        <path d="M11.354 6.354a.5.5 0 0 0-.708 0L8 8.893l-2.646-2.647a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0 0-.708z"/>
                                    </svg>
                                    JavaScript
                                </li>
                                <li class="skill-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2z"/>
                                        <path d="M8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
                                    </svg>
                                    PHP
                                </li>
                                <li class="skill-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm15 0a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2z"/>
                                        <path d="M3 13.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5zm0-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm0-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z"/>
                                    </svg>
                                    MySQL
                                </li>
                                <li class="skill-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                    </svg>
                                    Web Security
                                </li>
                                <li class="skill-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
                                        <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/>
                                    </svg>
                                    UI/UX Design
                                </li>
                            </ul>
                        </div>
                    </section>
                    
                    <section class="profile-section">
                        <h3 class="section-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                            </svg>
                            Contact
                        </h3>
                        <div class="section-content">
                            <ul class="contact-list">
                                <li class="contact-item">
                                    <div class="contact-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586l-1.239-.757Zm3.436-.586L16 11.801V4.697l-5.803 3.546Z"/>
                                        </svg>
                                    </div>
                                    <div class="contact-info">
                                        <p class="contact-label">Email</p>
                                        <p class="contact-value">parker@example.com</p>
                                    </div>
                                </li>
                                <li class="contact-item">
                                    <div class="contact-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                                        </svg>
                                    </div>
                                    <div class="contact-info">
                                        <p class="contact-label">Phone</p>
                                        <p class="contact-value">(555) 123-4567</p>
                                    </div>
                                </li>
                                <li class="contact-item">
                                    <div class="contact-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zm3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/>
                                    </div>
                                    <div class="contact-info">
                                        <p class="contact-label">Website</p>
                                        <p class="contact-value">parker.example.com</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Animation effects for profile elements
        document.addEventListener('DOMContentLoaded', function() {
            // Animate profile sections on load
            const sections = document.querySelectorAll('.profile-section');
            sections.forEach((section, index) => {
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
                section.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
                
                setTimeout(() => {
                    section.style.opacity = '1';
                    section.style.transform = 'translateY(0)';
                }, 100);
            });
            
            // Animate skill items on hover
            const skillItems = document.querySelectorAll('.skill-item');
            skillItems.forEach(item => {
                item.addEventListener('mouseover', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.boxShadow = 'var(--shadow-md)';
                });
                
                item.addEventListener('mouseout', function() {
                    this.style.transform = '';
                    this.style.boxShadow = '';
                });
            });
            
            // Add keyboard navigation
            document.addEventListener('keydown', function(e) {
                // Alt+H or Option+H to go home
                if ((e.altKey || e.metaKey) && e.key === 'h') {
                    window.location.href = 'index.php';
                    e.preventDefault();
                }
                
                // Alt+F or Option+F to go to file viewer
                if ((e.altKey || e.metaKey) && e.key === 'f') {
                    window.location.href = 'file-viewer.php';
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>