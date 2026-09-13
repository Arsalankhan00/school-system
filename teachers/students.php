<?php
require_once '../../includes/db.php';
require_once '../../includes/header.php';

$teacher_id = $_GET['id'] ?? 0;

// Fetch teacher details
$stmt = $conn->prepare("SELECT name FROM teachers WHERE id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

if (!$teacher) {
    echo "<div class='alert alert-danger'>Teacher not found.</div>";
    require_once '../../includes/footer.php';
    exit();
}

// Fetch students assigned to this teacher via courses
// Teacher -> teacher_courses -> courses -> student_courses -> students
$query = "SELECT DISTINCT s.id, s.name, s.roll_no, s.class_name, c.course_name 
          FROM students s
          JOIN student_courses sc ON s.id = sc.student_id
          JOIN courses c ON sc.course_id = c.id
          JOIN teacher_courses tc ON c.id = tc.course_id
          WHERE tc.teacher_id = ?
          ORDER BY c.course_name ASC, s.name ASC";

$stmt_students = $conn->prepare($query);
$stmt_students->bind_param("i", $teacher_id);
$stmt_students->execute();
$students = $stmt_students->get_result();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Students Assigned to: <?php echo htmlspecialchars($teacher['name']); ?></h4>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Teachers</a>
</div>

<div class="card-table p-4 mt-0">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Course</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students && $students->num_rows > 0): ?>
                    <?php while ($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['roll_no']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['course_name']); ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center text-muted">No students assigned to this teacher yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
