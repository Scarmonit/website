<?php
// Include configuration
require_once 'config.php';

// Check if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["passphrase"]) && $_POST["passphrase"] === AUTH_PASSPHRASE) {
        // Set cookie for authentication with more secure flags
        setcookie("parker_authenticated", "true", [
            'expires' => time() + 3600,
            'path' => "/parker/",
            'httponly' => true,
            'secure' => true
        ]);

        // Fix: Changed redirect from me.html to me.php
        header("Location: /parker/me.php");
        exit;
    } else {
        $error = "Incorrect passphrase";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parker - Authentication Required</title>
    <link rel="stylesheet" href="/parker/assets/css/style.css">
    <style>
        .login-container {
            background: white;
            padding: 30px;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-md);
            max-width: 400px;
            width: 100%;
            margin: 10vh auto;
        }

        .login-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-title {
            color: var(--dark);
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .login-subtitle {
            color: var(--text-medium);
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
        }

        .passphrase-input {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--light-accent);
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: border-color var(--transition-normal) ease;
        }

        .passphrase-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(58, 134, 255, 0.2);
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color var(--transition-normal) ease;
        }

        .submit-btn:hover,
        .submit-btn:focus {
            background-color: var(--primary-dark);
        }

        .error-message {
            color: var(--danger);
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background-color: rgba(229, 62, 62, 0.1);
            border-radius: var(--border-radius);
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <h1 class="login-title">Parker Directory</h1>
            <p class="login-subtitle">Authentication Required</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message" role="alert"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="passphrase" class="form-label">Enter Passphrase</label>
                <input type="password" id="passphrase" name="passphrase" class="passphrase-input" placeholder="Enter passphrase" required autocomplete="current-password">
            </div>
            <button type="submit" class="submit-btn">Access Directory</button>
        </form>
    </div>
</body>
</html>