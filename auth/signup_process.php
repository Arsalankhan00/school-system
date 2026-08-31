<?php
// Start session and connect to the database
session_start();
require_once '../includes/db.php';

// Check if the form was submitted (the user clicked "Sign Up")
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Grab the data the user typed in
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // --- Validation ---
    // Check if another admin is already using this username or email
    $check = $conn->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    
    // If we find a match, send them back to the signup page with an error message in the URL
    if ($check->get_result()->num_rows > 0) {
        // 'urlencode' ensures the error text is safe to put in a URL
        header("Location: ../signup.php?error=" . urlencode("Username or email already exists."));
        exit();
    } else {
        // --- Security ---
        // NEVER save plain text passwords in the database!
        // password_hash() converts 'myPassword123' into a scrambled code that hackers can't read.
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare to insert the new admin into the database
        $stmt = $conn->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
        // Bind the variables. 'sss' means 3 Strings. Note: we are saving the HASHED password, not the real one!
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        
        // Try to save them to the database
        if ($stmt->execute()) {
            // Success! Send them back to the signup page with a success message in the URL
            header("Location: ../signup.php?success=1");
            exit();
        } else {
            // If the database fails, send them back with a database error
            header("Location: ../signup.php?error=" . urlencode("Error creating account: " . $conn->error));
            exit();
        }
    }
} else {
    // If someone tries to visit this page directly (without submitting the form), just send them to signup
    header("Location: ../signup.php");
    exit();
}
?>
