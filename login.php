<?php
session_start();
$correct_passphrase = "44747"; // Change this to your desired passphrase

// Check if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["passphrase"]) && $_POST["passphrase"] === $correct_passphrase) {
        // Set cookie for authentication
        setcookie("parker_authenticated", "true", time() + 3600, "/parker/"); // Cookie lasts 1 hour

        // Always redirect to me.html after successful authentication
        header("Location: /parker/me.html");
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Authentication Required</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post">
            <div>
                <input type="password" name="passphrase" placeholder="Enter passphrase" required>
            </div>
            <button type="submit">Submit</button>
        </form>
    </div>
</body>

</html>