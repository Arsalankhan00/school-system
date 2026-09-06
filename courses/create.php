<?php
// Connect to the database and check if the user is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// Prepare empty variables for messages
$error = '';
$success = '';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Grab inputs from the form
    $course_name = $_POST['course_name'];
    $course_code = $_POST['course_code'];
    $department = $_POST['department'];
    $credits = $_POST['credits'];

    // Check if the Course Code is already taken by another course
    $check = $conn->prepare("SELECT id FROM courses WHERE course_code = ?");
    $check->bind_param("s", $course_code);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $error = "Course code already exists!";
    } else {
        // If the code is unique, insert the new course into the database
        $stmt = $conn->prepare("INSERT INTO courses (course_name, course_code, department, credits) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $course_name, $course_code, $department, $credits);
        
        if ($stmt->execute()) {
            $success = "Course added successfully!";
        } else {
            $error = "Error adding course: " . $conn->error;
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Add New Course</h4>
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
                <label class="form-label">Course Name</label>
                <input type="text" name="course_name" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Course Code</label>
                <input type="text" name="course_code" class="form-control" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label">Credit Hours</label>
                <input type="number" name="credits" class="form-control" min="0" value="0" required>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-2"></i>Save Course</button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
