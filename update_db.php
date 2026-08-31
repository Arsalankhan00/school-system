<?php
// Database connection
require_once 'includes/db.php';

// SQL to add the credits column
$sql = "ALTER TABLE courses ADD COLUMN credits INT DEFAULT 0 AFTER department";

echo "<h2>Database Migration</h2>";

if ($conn->query($sql)) {
    echo "<div style='color: green; font-weight: bold;'>Column 'credits' added successfully!</div>";
} else {
    echo "<div style='color: red; font-weight: bold;'>Error adding column: " . $conn->error . "</div>";
    echo "<p>Note: This might be because the column already exists.</p>";
}

echo "<br><a href='modules/courses/index.php'>Go to Courses Management</a>";
?>
