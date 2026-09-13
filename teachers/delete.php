<?php
// Start the session to check if the user is logged in
session_start();

// If the user is NOT logged in, send them back to the login page.
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../../login.php");
    exit();
}

// Connect to the database
require_once '../../includes/db.php';

// Get the ID of the teacher to delete from the URL (e.g., delete.php?id=3)
$id = $_GET['id'] ?? 0;

// Check if a valid ID was actually provided
if ($id) {
    // Prepare a DELETE query
    $stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
    
    // Bind the ID variable ('i' stands for Integer/Number)
    $stmt->bind_param("i", $id);
    
    // Execute the deletion!
    $stmt->execute();
}

// Once the deletion is done, redirect the user back to the teachers list
header("Location: index.php");
exit();
?>
