<?php
// Start the session to check if the user is logged in
session_start();

// If the user is NOT logged in, send them back to the login page.
// We do this manually here (instead of requiring header.php) because this page doesn't have any HTML to display!
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../../login.php");
    exit();
}

// Connect to the database
require_once '../../includes/db.php';

// Get the ID of the student to delete from the URL (e.g., delete.php?id=5)
$id = $_GET['id'] ?? 0;

// Check if a valid ID was actually provided
if ($id) {
    // Prepare a DELETE query. 
    // WARNING: Always use WHERE id=? when deleting, otherwise you might delete the entire table!
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    
    // Bind the ID variable ('i' stands for Integer/Number)
    $stmt->bind_param("i", $id);
    
    // Execute the deletion!
    $stmt->execute();
}

// Once the deletion is done (or if no ID was provided), redirect the user back to the students list.
header("Location: index.php");
exit(); // Always exit after a redirect.
?>
