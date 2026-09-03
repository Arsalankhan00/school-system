<?php
// Check if a 'session' has already been started. A session lets us store variables (like who is logged in) across multiple pages.
// PHP_SESSION_NONE means no session exists yet.
if (session_status() == PHP_SESSION_NONE) {
    // If no session exists, we start one so we can remember the logged-in user.
    session_start();
}

// Check if the 'admin_logged_in' variable is missing from our session.
// The exclamation mark (!) means 'NOT'. So this reads: If NOT set session 'admin_logged_in'.
if (!isset($_SESSION['admin_logged_in'])) {
    // If the user is NOT logged in, we forcefully redirect them back to the login page.
    header("Location: login.php");
    // 'exit()' stops the rest of this file from loading so an unauthenticated user sees nothing.
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System - Admin Panel</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/SMS/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Here we include the sidebar menu code from another file so we don't have to copy-paste it on every page -->
    <?php include 'sidebar.php'; ?>
    
    <div id="main-content">
        <div class="top-navbar">
            <div class="d-flex align-items-center">
                <button id="sidebarToggle" class="btn text-dark me-3 border-0 p-0" style="font-size: 1.4rem; background: transparent;">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 class="page-title mb-0">Dashboard</h2>
            </div>
            <div class="user-profile">
                <div class="user-info text-end">
                    <!-- We use 'echo' to print the name of the admin directly onto the webpage -->
                    <!-- htmlspecialchars() is a security feature that cleans the text to prevent hacking (XSS attacks) -->
                    <p class="name"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
                    <p class="role">Administrator</p>
                </div>
                <img src="https://ui-avatars.com/api/?name=Admin&background=4F46E5&color=fff" alt="Admin">
                <a href="/SMS/auth/logout.php" class="btn btn-sm btn-outline-danger ms-3"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="page-content">
