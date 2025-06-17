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
    <title>Parker - Welcome</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            color: #333;
        }
    </style>
</head>

<body>
    <h1>Welcome to the Parker Directory</h1>
    <p>You have successfully authenticated.</p>

    <?php
    // Display directory contents
    $dir = "./";
    $files = scandir($dir);
    echo "<h2>Directory Contents:</h2>";
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != "." && $file != ".." && $file != "login.php" && $file != ".htaccess") {
            echo "<li><a href='" . htmlspecialchars($file) . "'>" . htmlspecialchars($file) . "</a></li>";
        }
    }
    echo "</ul>";
    ?>
</body>

</html>