<?php
// We define the location of the database server. 'localhost' means it is running on this exact computer.
$servername = "localhost";

// The username used to log into the database. 'root' is the default for XAMPP.
$username = "root";

// The password for the database. In XAMPP, this is usually left empty by default.
$password = "";

// The name of the specific database we created for this project.
$dbname = "sms_admin_db";

// Try to create a connection to the database using the details we just provided.
// 'new mysqli' is a built-in PHP command used to talk to MySQL databases.

$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection failed.
// 'connect_error' will hold an error message if something went wrong (like a wrong password or database name).
if ($conn->connect_error) {
  // If it failed, the 'die' command stops the entire script immediately and prints the error message on the screen.
  die("Connection failed: " . $conn->connect_error);
}

// If the code reaches this line, it means the connection was 100% successful! 
// Other files will 'require' or 'include' this file so they can use the '$conn' variable to fetch data.

function log_student_event($student_id, $event_type, $description) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO student_history (student_id, event_type, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $student_id, $event_type, $description);
    return $stmt->execute();
}
?>
