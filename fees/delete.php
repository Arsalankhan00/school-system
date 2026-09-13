<?php
require_once '../../includes/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM fees WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: index.php?success=Fee record deleted successfully");
    } else {
        header("Location: index.php?error=Error deleting record");
    }
} else {
    header("Location: index.php");
}
?>
