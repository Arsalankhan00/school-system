<?php
// Connect to the database and check if logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

$error = '';
$success = '';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Grab the student_id and course_id from the dropdown menus
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];

    // Make sure both dropdowns were actually selected
    if (empty($student_id) || empty($course_id)) {
        $error = "Please select both a student and a course.";
    } else {
        // Check if this student is ALREADY enrolled in this course
        $check_stmt = $conn->prepare("SELECT id FROM student_courses WHERE student_id = ? AND course_id = ?");
        $check_stmt->bind_param("ii", $student_id, $course_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "This student is already enrolled in this course.";
        } else {
            // If it's a new enrollment, insert into the database
            $stmt = $conn->prepare("INSERT INTO student_courses (student_id, course_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $student_id, $course_id);
            
            if ($stmt->execute()) {
                // Fetch course details for history logging
                $course_stmt = $conn->prepare("SELECT course_name, course_code FROM courses WHERE id = ?");
                $course_stmt->bind_param("i", $course_id);
                $course_stmt->execute();
                $course_res = $course_stmt->get_result()->fetch_assoc();
                
                if ($course_res) {
                    $desc = "Enrolled in course: " . $course_res['course_name'] . " (" . $course_res['course_code'] . ")";
                    log_student_event($student_id, 'Course Enrollment', $desc);
                }
                $success = "Course assigned to student successfully!";
            } else {
                $error = "Error assigning course: " . $conn->error;
            }
        }
    }
}

// Fetch Dropdown Data
$students = $conn->query("SELECT id, name, roll_no FROM students WHERE status = 'Active' ORDER BY name ASC");
$courses = $conn->query("SELECT id, course_name, course_code FROM courses ORDER BY course_name ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Assign Course to Student</h4>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Allocations</a>
</div>

<div class="card-table p-4 mt-0">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="student_create.php" method="POST">
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="form-label">Select Student <span class="text-danger">*</span></label>
                <select name="student_id" class="form-select" required>
                    <option value="">-- Choose Student --</option>
                    <?php if ($students && $students->num_rows > 0): ?>
                        <?php while ($s = $students->fetch_assoc()): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']) . " (" . htmlspecialchars($s['roll_no']) . ")"; ?></option>
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
                <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-2"></i>Enroll Student</button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
