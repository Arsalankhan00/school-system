<?php
// Connect to the database and check if the user is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// Get the student's ID from the URL (e.g., edit.php?id=5).
// If there is no ID in the URL, default to 0.
$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Check if the user clicked "Update Student" to submit the form.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Grab all the updated text the user typed into the form.
    // Notice we also grab the hidden 'id' from the form so we know exactly WHO to update.
    $id = $_POST['id'];
    $name = $_POST['name'];
    $father_name = $_POST['father_name'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    
    $class_id = $_POST['class_id'];
    $class_name = '';
    $section = '';
    $class_stmt = $conn->prepare("SELECT class_name, section FROM classes WHERE id = ?");
    $class_stmt->bind_param("i", $class_id);
    $class_stmt->execute();
    $class_res = $class_stmt->get_result()->fetch_assoc();
    if ($class_res) {
        $class_name = $class_res['class_name'];
        $section = $class_res['section'];
    }

    $roll_no = $_POST['roll_no'];
    $status = $_POST['status'];

    // Check if another student is already using this new Roll Number or Email.
    // 'id != ?' means "ignore the current student's ID" (so they don't trigger an error against their own old data).
    $check = $conn->prepare("SELECT id FROM students WHERE (roll_no = ? OR email = ?) AND id != ?");
    $check->bind_param("ssi", $roll_no, $email, $id);
    $check->execute();
    
    // If we find a match, show an error.
    if ($check->get_result()->num_rows > 0) {
        $error = "Roll Number or Email already exists for another student!";
    } else {
        // Fetch the old status first for change logging
        $old_status_stmt = $conn->prepare("SELECT status FROM students WHERE id = ?");
        $old_status_stmt->bind_param("i", $id);
        $old_status_stmt->execute();
        $old_status_res = $old_status_stmt->get_result()->fetch_assoc();
        $old_status = $old_status_res ? $old_status_res['status'] : '';

        // If it's safe to update, prepare an UPDATE query.
        // SET tells the database which columns to change. WHERE id=? tells it exactly which row to change.
        $stmt = $conn->prepare("UPDATE students SET name=?, father_name=?, contact=?, email=?, address=?, class_name=?, section=?, roll_no=?, status=? WHERE id=?");
        
        // Bind the variables. 'sssssssssi' means 9 Strings and 1 Integer (the ID at the end).
        $stmt->bind_param("sssssssssi", $name, $father_name, $contact, $email, $address, $class_name, $section, $roll_no, $status, $id);
        
        // Execute the update!
        if ($stmt->execute()) {
            if ($old_status != $status) {
                log_student_event($id, 'Status Change', "Status changed from " . $old_status . " to " . $status);
            }
            $success = "Student updated successfully!";
        } else {
            $error = "Error updating student: " . $conn->error;
        }
    }
}

// Below this line runs BEFORE the user submits the form (when they first load the page).
// We fetch the current data from the database so we can pre-fill the form boxes.
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Fetch the single row as an array called '$student'.
$student = $stmt->get_result()->fetch_assoc();

// If the database didn't find any student with that ID, show an error message and stop loading the page.
if (!$student) {
    echo "<div class='alert alert-danger'>Student not found.</div>";
    require_once '../../includes/footer.php';
    exit();
}

// Fetch all classes for the dropdown
$classes_list = $conn->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Edit Student</h4>
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
        <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($student['name']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Father's Name</label>
                <input type="text" name="father_name" class="form-control" value="<?php echo htmlspecialchars($student['father_name']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Roll Number</label>
                <input type="text" name="roll_no" class="form-control" value="<?php echo htmlspecialchars($student['roll_no']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Class & Section <span class="text-danger">*</span></label>
                <select name="class_id" class="form-select" required>
                    <option value="">-- Select Class & Section --</option>
                    <?php if ($classes_list && $classes_list->num_rows > 0): ?>
                        <?php while ($c = $classes_list->fetch_assoc()): ?>
                            <?php 
                            $selected = ($student['class_name'] == $c['class_name'] && 
                                         ($student['section'] == $c['section'] || (empty($student['section']) && empty($c['section'])))) ? 'selected' : '';
                            ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($c['class_name']) . (!empty($c['section']) ? " (" . htmlspecialchars($c['section']) . ")" : ""); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($student['contact']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Admission Status</label>
                <select name="status" class="form-select">
                    <option value="Active" <?php echo ($student['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo ($student['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                    <option value="Graduated" <?php echo ($student['status'] == 'Graduated') ? 'selected' : ''; ?>>Graduated</option>
                    <option value="Dropped" <?php echo ($student['status'] == 'Dropped') ? 'selected' : ''; ?>>Dropped</option>
                </select>
            </div>
            <div class="col-md-12 mb-4">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($student['address']); ?></textarea>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-2"></i>Update Student</button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
