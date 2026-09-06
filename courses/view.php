<?php
// Connect to the database and verify the admin is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// Get the course's ID from the URL. If not provided, default to 0.
$id = $_GET['id'] ?? 0;

// Fetch the course's details
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

// If the course doesn't exist, show an error and exit
if (!$course) {
    echo "<div class='alert alert-danger'>Course not found.</div>";
    require_once '../../includes/footer.php';
    exit();
}

// Fetch teachers assigned to this course
$teachers_stmt = $conn->prepare("
    SELECT t.id, t.name, t.email, t.department 
    FROM teacher_courses tc 
    JOIN teachers t ON tc.teacher_id = t.id 
    WHERE tc.course_id = ? 
    ORDER BY t.name ASC
");
$teachers_stmt->bind_param("i", $id);
$teachers_stmt->execute();
$assigned_teachers = $teachers_stmt->get_result();

// Fetch students enrolled in this course
$students_stmt = $conn->prepare("
    SELECT s.id, s.name, s.roll_no, s.class_name, s.status 
    FROM student_courses sc 
    JOIN students s ON sc.student_id = s.id 
    WHERE sc.course_id = ? 
    ORDER BY s.name ASC
");
$students_stmt->bind_param("i", $id);
$students_stmt->execute();
$enrolled_students = $students_stmt->get_result();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Course Details</h4>
    <div>
        <a href="index.php" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
        <a href="edit.php?id=<?php echo $course['id']; ?>" class="btn btn-primary-custom"><i class="fas fa-edit me-2"></i>Edit Course</a>
    </div>
</div>

<div class="row">
    <!-- Course Information -->
    <div class="col-md-12 mb-4">
        <div class="card-table p-4 mt-0">
            <div class="row align-items-center">
                <div class="col-md-2 text-center text-md-start mb-3 mb-md-0">
                    <div class="d-inline-flex align-items-center justify-content-center bg-gradient-warning text-white rounded-circle" style="width: 80px; height: 80px; font-size: 2.2rem; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                <div class="col-md-10">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($course['course_name']); ?></h4>
                            <p class="text-muted mb-0">Course Code: <span class="badge bg-secondary"><?php echo htmlspecialchars($course['course_code']); ?></span></p>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <span class="fs-5 text-muted me-3">Department: <strong class="text-dark"><?php echo htmlspecialchars($course['department'] ?? 'General'); ?></strong></span>
                            <span class="fs-5 text-muted">Credits: <strong class="text-dark"><?php echo htmlspecialchars($course['credits'] ?? 0); ?> hrs</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Assigned Teachers Card -->
    <div class="col-md-5 mb-4">
        <div class="card-table p-4 mt-0 h-100">
            <h5 class="fw-bold mb-4"><i class="fas fa-chalkboard-teacher me-2 text-success"></i>Assigned Teachers</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Department</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($assigned_teachers && $assigned_teachers->num_rows > 0): ?>
                            <?php while ($teacher = $assigned_teachers->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <a href="../teachers/view.php?id=<?php echo $teacher['id']; ?>" class="text-decoration-none fw-bold text-dark">
                                            <?php echo htmlspecialchars($teacher['name']); ?>
                                        </a>
                                    </td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($teacher['department'] ?? ''); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">No teachers assigned to this course yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3">
                <a href="../allocations/create.php" class="btn btn-sm btn-outline-success"><i class="fas fa-plus me-1"></i> Assign Teacher</a>
            </div>
        </div>
    </div>

    <!-- Enrolled Students Card -->
    <div class="col-md-7 mb-4">
        <div class="card-table p-4 mt-0 h-100">
            <h5 class="fw-bold mb-4"><i class="fas fa-user-graduate me-2 text-primary"></i>Enrolled Students</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($enrolled_students && $enrolled_students->num_rows > 0): ?>
                            <?php while ($student = $enrolled_students->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
                                    <td>
                                        <a href="../students/view.php?id=<?php echo $student['id']; ?>" class="text-decoration-none fw-bold text-dark">
                                            <?php echo htmlspecialchars($student['name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['class_name']); ?></td>
                                    <td>
                                        <?php
                                        $status_class = 'bg-secondary';
                                        if ($student['status'] == 'Active') $status_class = 'bg-success';
                                        elseif ($student['status'] == 'Inactive') $status_class = 'bg-danger';
                                        elseif ($student['status'] == 'Graduated') $status_class = 'bg-info';
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($student['status']); ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No students enrolled in this course yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3">
                <a href="../allocations/student_create.php" class="btn btn-sm btn-outline-primary"><i class="fas fa-plus me-1"></i> Enroll Student</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
