<?php
// --- Database Setup Script ---
// This file is used to create the database tables if they don't exist yet.

$servername = "localhost"; // Usually 'localhost' for XAMPP
$username = "root";        // Default XAMPP username
$password = "";            // Default XAMPP password is empty

// Create a direct connection to MySQL
$conn = new mysqli($servername, $username, $password);

// Check if the connection worked
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Read the SQL script
$sql_script = file_get_contents(__DIR__ . '/database.sql');

if ($conn->multi_query($sql_script)) {
  do {
    // Store first result set
    if ($result = $conn->store_result()) {
      $result->free();
    }
    // Prepare next
  } while ($conn->more_results() && $conn->next_result());
  echo "Database and tables created successfully.";
} else {
  echo "Error creating database/tables: " . $conn->error;
}

// Insert Default Admin
$conn->select_db('sms_admin_db');
$admin_user = 'admin';
$admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
$admin_email = 'admin@school.com';
$check_admin = $conn->query("SELECT * FROM admins WHERE username='$admin_user'");
if($check_admin->num_rows == 0){
    $stmt = $conn->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $admin_user, $admin_pass, $admin_email);
    if($stmt->execute()){
        echo "Default admin inserted.";
    }
}

$conn->close();
?>
