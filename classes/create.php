<?php
// Connect to the database and check if the user is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_name = trim($_POST['class_name']);
    $section = trim($_POST['section']);
    if (empty($section)) {
        $section = null; // Store NULL in DB for empty section
    }

    if (empty($class_name)) {
        $error = "Class name is required.";
    } else {
        // Check for duplicates
        if ($section === null) {
            $stmt = $conn->prepare("SELECT id FROM classes WHERE class_name = ? AND section IS NULL");
            $stmt->bind_param("s", $class_name);
        } else {
            $stmt = $conn->prepare("SELECT id FROM classes WHERE class_name = ? AND section = ?");
            $stmt->bind_param("ss", $class_name, $section);
        }
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = "This class and section combination already exists!";
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO classes (class_name, section) VALUES (?, ?)");
            $stmt->bind_param("ss", $class_name, $section);
            
            if ($stmt->execute()) {
                $success = "Class added successfully!";
            } else {
                $error = "Error adding class: " . $conn->error;
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Add New Class</h4>
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
                <label class="form-label">Class Name <span class="text-danger">*</span></label>
                <input type="text" name="class_name" class="form-control" placeholder="e.g. Class 10" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label">Section (Optional)</label>
                <input type="text" name="section" class="form-control" placeholder="e.g. Section A">
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-2"></i>Save Class</button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
