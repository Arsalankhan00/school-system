<?php
// Connect to the database and load the header (which checks if you are logged in)
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// Create blank variables to hold success or error messages later.
$error = '';
$success = '';

// Check if the user clicked the "Save Student" button to submit the form.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Grab all the text the user typed into the input boxes.
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

    // Before we save, let's check if a student with this exact Roll Number OR Email already exists!
    // We don't want duplicate students.
    $check = $conn->prepare("SELECT id FROM students WHERE roll_no = ? OR email = ?");
    $check->bind_param("ss", $roll_no, $email);
    $check->execute();
    
    // If num_rows is greater than 0, it means we found a match in the database.
    if ($check->get_result()->num_rows > 0) {
        // Show an error to the user and stop saving.
        $error = "Roll Number or Email already exists!";
    } else {
        // If it is a brand new student, prepare an INSERT query to add them to the 'students' table.
        // We use 9 question marks (?) because we are inserting 9 pieces of data.
        $stmt = $conn->prepare("INSERT INTO students (name, father_name, contact, email, address, class_name, section, roll_no, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Attach the 9 variables to the 9 question marks. "sssssssss" means all 9 are Strings (text).
        $stmt->bind_param("sssssssss", $name, $father_name, $contact, $email, $address, $class_name, $section, $roll_no, $status);
        
        // Try to execute the save.
        if ($stmt->execute()) {
            $new_student_id = $stmt->insert_id;
            log_student_event($new_student_id, 'Registration', "Student registered with initial status: " . $status);
            $success = "Student added successfully!";
        } else {
            $error = "Error adding student: " . $conn->error;
        }
    }
}

// Fetch all active classes for the dropdown
$classes_list = $conn->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Add New Student</h4>
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
                <label class="form-label">Father's Name</label>
                <input type="text" name="father_name" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Roll Number</label>
                <input type="text" name="roll_no" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Class & Section <span class="text-danger">*</span></label>
                <select name="class_id" class="form-select" required>
                    <option value="">-- Select Class & Section --</option>
                    <?php if ($classes_list && $classes_list->num_rows > 0): ?>
                        <?php while ($c = $classes_list->fetch_assoc()): ?>
                            <option value="<?php echo $c['id']; ?>">
                                <?php echo htmlspecialchars($c['class_name']) . (!empty($c['section']) ? " (" . htmlspecialchars($c['section']) . ")" : ""); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Admission Status</label>
                <select name="status" class="form-select">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Graduated">Graduated</option>
                    <option value="Dropped">Dropped</option>
                </select>
            </div>
            <div class="col-md-12 mb-4">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-2"></i>Save Student</button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
