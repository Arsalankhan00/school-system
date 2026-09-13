<?php
// Connect to the database and ensure the user is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// Variables to hold success or error messages to show the user later
$error = '';
$success = '';

// Check if the form was submitted (the user clicked "Save Teacher")
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Grab the inputs from the form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $department = $_POST['department'];
    $status = $_POST['status'];

    // Check if a teacher with this email already exists in the database
    $check = $conn->prepare("SELECT id FROM teachers WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        // If the email is found, show an error message
        $error = "Email already exists!";
    } else {
        // If the email is unique, prepare to INSERT the new teacher's data
        // We use 5 question marks because we have 5 pieces of data to insert
        $stmt = $conn->prepare("INSERT INTO teachers (name, email, contact, department, status) VALUES (?, ?, ?, ?, ?)");
        
        // Attach the 5 variables to the 5 question marks. 'sssss' means all are Strings
        $stmt->bind_param("sssss", $name, $email, $contact, $department, $status);
        
        // Try to execute the query
        if ($stmt->execute()) {
            $success = "Teacher added successfully!";
        } else {
            $error = "Error adding teacher: " . $conn->error;
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Add New Teacher</h4>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
</div>

<div class="card-table p-4 mt-0">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="create.php" method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact" class="form-control" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="Active">Active</option>
                    <option value="On Leave">On Leave</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-2"></i>Save Teacher</button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
