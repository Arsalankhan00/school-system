<?php
// Check if the user is logged in
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../../login.php");
    exit();
}
require_once '../../includes/db.php';

// Get the ID of the timetable record to delete
$id = $_GET['id'] ?? 0;

if ($id) {
    // Delete the schedule
    $stmt = $conn->prepare("DELETE FROM timetable WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Redirect back to the timetable page
header("Location: index.php");
exit();
?>
