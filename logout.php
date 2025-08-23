<?php
// Clear the authentication cookie with secure flags
setcookie("parker_authenticated", "", [
    'expires' => time() - 3600,
    'path' => "/parker/",
    'httponly' => true,
    'secure' => true
]);

// Redirect to login page
header("Location: /parker/login.php");
exit;
?>