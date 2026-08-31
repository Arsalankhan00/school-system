<?php
// Start the session so we know which session to destroy
session_start();

// Remove all session variables (like 'admin_logged_in', 'admin_id')
session_unset();

// Completely destroy the session on the server
session_destroy();

// Redirect the user back to the login page
header("Location: ../login.php");
exit(); // Always exit after a redirect
?>
