<?php
// Secure the delete action
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../includes/db.php';

// Check if an ID was passed in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Delete the allocation (assignment) record from the database
    $stmt = $conn->prepare("DELETE FROM teacher_courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Redirect back to the allocations list
header("Location: index.php");
exit();
?>
