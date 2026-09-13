<?php
// Connect to the database and ensure the user is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// Get the ID of the teacher to edit from the URL (e.g., edit.php?id=3)
$id = $_GET['id'] ?? 0;

// Variables to hold success or error messages
$error = '';
$success = '';

// Check if the user submitted the form to update the teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Grab the updated values from the form inputs
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $department = $_POST['department'];
    $status = $_POST['status'];

    // Make sure the newly typed email isn't already taken by a DIFFERENT teacher
    // 'id != ?' ensures we don't accidentally throw an error for the teacher's own current email
    $check = $conn->prepare("SELECT id FROM teachers WHERE email = ? AND id != ?");
    $check->bind_param("si", $email, $id);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $error = "Email already exists for another teacher!";
    } else {
        // If the email is okay, prepare an UPDATE query to save the new data
        $stmt = $conn->prepare("UPDATE teachers SET name=?, email=?, contact=?, department=?, status=? WHERE id=?");
        
        // Bind the variables: 5 Strings ('s') and 1 Integer ('i') for the ID
        $stmt->bind_param("sssssi", $name, $email, $contact, $department, $status, $id);
        
        if ($stmt->execute()) {
            $success = "Teacher updated successfully!";
        } else {
            $error = "Error updating teacher: " . $conn->error;
        }
    }
}

// --- Fetch Current Data ---
// When the page first loads, we need to fetch the teacher's current details from the database
// so we can put them into the form boxes for the user to edit.
$stmt = $conn->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Get the result as an array called $teacher
$teacher = $stmt->get_result()->fetch_assoc();

// If no teacher is found with that ID, show an error and stop loading the rest of the page
if (!$teacher) {
    echo "<div class='alert alert-danger'>Teacher not found.</div>";
    require_once '../../includes/footer.php';
    exit();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Edit Teacher</h4>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
</div>

<div class="card-table p-4 mt-0">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="edit.php?id=<?php echo $id; ?>" method="POST">
        <input type="hidden" name="id" value="<?php echo $teacher['id']; ?>">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($teacher['name']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($teacher['contact']); ?>" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($teacher['department'] ?? ''); ?>" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="Active" <?php echo ($teacher['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="On Leave" <?php echo ($teacher['status'] == 'On Leave') ? 'selected' : ''; ?>>On Leave</option>
                    <option value="Inactive" <?php echo ($teacher['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-2"></i>Update Teacher</button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
