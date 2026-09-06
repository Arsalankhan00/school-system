<?php
// Connect to the database and check if the user is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// Get the ID of the course from the URL
$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Check if the form was submitted to update the course
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $course_name = $_POST['course_name'];
    $course_code = $_POST['course_code'];
    $department = $_POST['department'];
    $credits = $_POST['credits'];

    // Ensure the new course code isn't taken by a DIFFERENT course
    $check = $conn->prepare("SELECT id FROM courses WHERE course_code = ? AND id != ?");
    $check->bind_param("si", $course_code, $id);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $error = "Course code already exists for another course!";
    } else {
        // Update the existing record
        $stmt = $conn->prepare("UPDATE courses SET course_name=?, course_code=?, department=?, credits=? WHERE id=?");
        $stmt->bind_param("sssii", $course_name, $course_code, $department, $credits, $id);
        
        if ($stmt->execute()) {
            $success = "Course updated successfully!";
        } else {
            $error = "Error updating course: " . $conn->error;
        }
    }
}

// Fetch the current data to pre-fill the form fields
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    echo "<div class='alert alert-danger'>Course not found.</div>";
    require_once '../../includes/footer.php';
    exit();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Edit Course</h4>
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
        <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Course Name</label>
                <input type="text" name="course_name" class="form-control" value="<?php echo htmlspecialchars($course['course_name']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Course Code</label>
                <input type="text" name="course_code" class="form-control" value="<?php echo htmlspecialchars($course['course_code']); ?>" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($course['department']); ?>" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label">Credit Hours</label>
                <input type="number" name="credits" class="form-control" min="0" value="<?php echo htmlspecialchars($course['credits'] ?? 0); ?>" required>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-2"></i>Update Course</button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
