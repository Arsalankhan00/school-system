<?php
// Connect to the database and check if logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

$error = '';
$success = '';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Grab the teacher_id and course_id from the dropdown menus
    $teacher_id = $_POST['teacher_id'];
    $course_id = $_POST['course_id'];

    // Make sure both dropdowns were actually selected
    if (empty($teacher_id) || empty($course_id)) {
        $error = "Please select both a teacher and a course.";
    } else {
        // Check if this exact teacher is ALREADY assigned to this exact course
        $check_stmt = $conn->prepare("SELECT id FROM teacher_courses WHERE teacher_id = ? AND course_id = ?");
        $check_stmt->bind_param("ii", $teacher_id, $course_id); // 'ii' means two Integers
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "This teacher is already assigned to this course.";
        } else {
            // If it's a new assignment, insert the ID numbers into the database
            $stmt = $conn->prepare("INSERT INTO teacher_courses (teacher_id, course_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $teacher_id, $course_id);
            
            if ($stmt->execute()) {
                $success = "Course assigned to teacher successfully!";
            } else {
                $error = "Error assigning course: " . $conn->error;
            }
        }
    }
}

// --- Fetch Dropdown Data ---
// When the page loads, we need to fetch all teachers and courses 
// so we can put them inside the <select> dropdown menus in the HTML form below.
$teachers = $conn->query("SELECT id, name FROM teachers ORDER BY name ASC");
$courses = $conn->query("SELECT id, course_name, course_code FROM courses ORDER BY course_name ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Assign Course to Teacher</h4>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Allocations</a>
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
            <div class="col-md-6 mb-4">
                <label class="form-label">Select Teacher <span class="text-danger">*</span></label>
                <select name="teacher_id" class="form-select" required>
                    <option value="">-- Choose Teacher --</option>
                    <?php if ($teachers && $teachers->num_rows > 0): ?>
                        <?php while ($t = $teachers->fetch_assoc()): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="col-md-6 mb-4">
                <label class="form-label">Select Course <span class="text-danger">*</span></label>
                <select name="course_id" class="form-select" required>
                    <option value="">-- Choose Course --</option>
                    <?php if ($courses && $courses->num_rows > 0): ?>
                        <?php while ($c = $courses->fetch_assoc()): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']) . " (" . htmlspecialchars($c['course_code']) . ")"; ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-2"></i>Assign Course</button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
