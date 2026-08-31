<?php
// Start a session to track if the user is already logged in
session_start();

// If the user is already logged in as an admin, they don't need to see the login page again!
// We instantly redirect them to the main dashboard (index.php)
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit(); // Always use exit() after redirecting to stop the rest of the page from loading
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-bg">
    <div class="login-card">
        <div class="text-center mb-4">
            <i class="fas fa-graduation-cap fa-3x text-primary mb-3" style="color: #818CF8 !important;"></i>
            <h3 class="fw-bold">SMS Admin Panel</h3>
            <p class="text-muted" style="color: #CBD5E1 !important;">Sign in to continue</p>
        </div>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert">
                Invalid username or password!
            </div>
        <?php endif; ?>

        <form action="auth/login_process.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-white"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control border-start-0" id="username" name="username" placeholder="Enter username" required>
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-white"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="Enter password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary-custom w-100 py-2">
                Sign In <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </form>
        
        <div class="text-center mt-3">
            <p class="text-muted mb-0" style="color: #CBD5E1 !important;">Don't have an account? <a href="signup.php" class="text-white text-decoration-none fw-bold">Sign up</a></p>
        </div>
    </div>
</body>
</html>
