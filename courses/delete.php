<?php
// Secure the delete action by checking if the user is logged in
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../../login.php");
    exit();
}
require_once '../../includes/db.php';

// Get the ID of the course to delete
$id = $_GET['id'] ?? 0;

if ($id) {
    // Delete the course from the database
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Redirect back to the course list
header("Location: index.php");
exit();
?>
