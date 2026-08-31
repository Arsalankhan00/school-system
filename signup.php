<?php
// Start a session to track if the user is already logged in
session_start();

// Just like the login page, if they are already logged in, redirect them to the dashboard
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Signup - School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-bg">
    <div class="login-card">
        <div class="text-center mb-4">
            <i class="fas fa-user-plus fa-3x text-primary mb-3" style="color: #818CF8 !important;"></i>
            <h3 class="fw-bold">Create Admin Account</h3>
            <p class="text-muted" style="color: #CBD5E1 !important;">Register a new administrator</p>
        </div>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" role="alert">
                Account created successfully! You can now <a href="login.php" class="alert-link">login</a>.
            </div>
        <?php endif; ?>

        <form action="auth/signup_process.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-white"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control border-start-0" id="username" name="username" placeholder="Choose a username" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-white"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="Enter your email" required>
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-white"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="Create a password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary-custom w-100 py-2">
                Sign Up <i class="fas fa-user-check ms-2"></i>
            </button>
        </form>
        
        <div class="text-center mt-3">
            <p class="text-muted mb-0" style="color: #CBD5E1 !important;">Already have an account? <a href="login.php" class="text-white text-decoration-none fw-bold">Login here</a></p>
        </div>
    </div>
</body>
</html>
