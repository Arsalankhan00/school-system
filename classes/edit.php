<?php
// Connect to the database and check if the user is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $class_name = trim($_POST['class_name']);
    $section = trim($_POST['section']);
    if (empty($section)) {
        $section = null;
    }

    if (empty($class_name)) {
        $error = "Class name is required.";
    } else {
        // Check for duplicates (excluding current class ID)
        if ($section === null) {
            $stmt = $conn->prepare("SELECT id FROM classes WHERE class_name = ? AND section IS NULL AND id != ?");
            $stmt->bind_param("si", $class_name, $id);
        } else {
            $stmt = $conn->prepare("SELECT id FROM classes WHERE class_name = ? AND section = ? AND id != ?");
            $stmt->bind_param("ssi", $class_name, $section, $id);
        }
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = "This class and section combination already exists for another class!";
        } else {
            // Update
            $stmt = $conn->prepare("UPDATE classes SET class_name = ?, section = ? WHERE id = ?");
            $stmt->bind_param("ssi", $class_name, $section, $id);
            
            if ($stmt->execute()) {
                $success = "Class updated successfully!";
            } else {
                $error = "Error updating class: " . $conn->error;
            }
        }
    }
}

// Fetch current details
$stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$class_data = $stmt->get_result()->fetch_assoc();

if (!$class_data) {
    echo "<div class='alert alert-danger'>Class not found.</div>";
    require_once '../../includes/footer.php';
    exit();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Edit Class</h4>
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
        <input type="hidden" name="id" value="<?php echo $class_data['id']; ?>">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Class Name <span class="text-danger">*</span></label>
                <input type="text" name="class_name" class="form-control" value="<?php echo htmlspecialchars($class_data['class_name']); ?>" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label">Section (Optional)</label>
                <input type="text" name="section" class="form-control" value="<?php echo htmlspecialchars($class_data['section'] ?? ''); ?>" placeholder="e.g. Section A">
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-2"></i>Update Class</button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
