<?php
require_once 'includes/db.php';

$sql = "ALTER TABLE courses ADD COLUMN credits INT DEFAULT 0 AFTER department";

if ($conn->query($sql)) {
    echo "Column 'credits' added successfully!";
} else {
    echo "Error adding column: " . $conn->error;
}
?>
