<?php
// Clear the authentication cookie
setcookie("parker_authenticated", "", time() - 3600, "/parker/");

// Redirect to login page
header("Location: /parker/login.php");
exit;
?>