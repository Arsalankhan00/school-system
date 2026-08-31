<?php
// We must start the session on this page so we can save the user's login status later.
session_start();

// Include the database connection so we can check if the user exists in our database.
require_once '../includes/db.php';

// Check if the form was actually submitted. 'POST' is the method used by HTML forms to send data securely.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Grab the username and password that the user typed into the login form.
    // $_POST is a special PHP variable that holds all data sent from an HTML form.
    $username = $_POST['username'];
    $password = $_POST['password'];

    // We prepare a SQL query to ask the database: "Find the id, username, and password for this specific username"
    // We use a question mark (?) instead of the actual username. This is called a "Prepared Statement".
    // Prepared statements protect your website from hackers trying to do "SQL Injection".
    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    
    // Here we bind (attach) the actual $username variable to that question mark. 's' means it is a String (text).
    $stmt->bind_param("s", $username);
    
    // Execute (run) the query against the database.
    $stmt->execute();
    
    // Get the results of the query.
    $result = $stmt->get_result();

    // Check if exactly 1 user was found with that username.
    if ($result->num_rows == 1) {
        
        // Grab that user's data from the database and put it into an array called $admin.
        $admin = $result->fetch_assoc();
        
        // Now we verify the password! 
        // password_verify() automatically checks if the password the user typed matches the scrambled/hashed password saved in the database.
        if (password_verify($password, $admin['password'])) {
            
            // Password is correct! We set session variables to officially log them in.
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            // Redirect the user to the main dashboard page.
            header("Location: ../index.php");
            exit(); // Always use exit() after a redirect to stop the rest of the script.
        } else {
            // If the password was wrong, redirect them back to the login page with an error code (error=1).
            header("Location: ../login.php?error=1");
            exit();
        }
    } else {
        // If NO user was found with that username, also send them back to the login page with an error.
        header("Location: ../login.php?error=1");
        exit();
    }
} else {
    // If someone tries to visit this page directly without submitting the form, kick them back to the login page.
    header("Location: ../login.php");
    exit();
}
?>
