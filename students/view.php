<?php
// Connect to the database and verify the admin is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// Get the student's ID from the URL. If not provided, default to 0.
$id = $_GET['id'] ?? 0;

// Fetch the student's details
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// If the student doesn't exist, show an error and exit
if (!$student) {
    echo "<div class='alert alert-danger'>Student not found.</div>";
    require_once '../../includes/footer.php';
    exit();
}

// Fetch enrolled courses for this student
$courses_stmt = $conn->prepare("
    SELECT c.id, c.course_name, c.course_code, c.credits, c.department 
    FROM student_courses sc 
    JOIN courses c ON sc.course_id = c.id 
    WHERE sc.student_id = ? 
    ORDER BY c.course_name ASC
");
$courses_stmt->bind_param("i", $id);
$courses_stmt->execute();
$enrolled_courses = $courses_stmt->get_result();

// Fetch student admission and course history
$history_stmt = $conn->prepare("SELECT * FROM student_history WHERE student_id = ? ORDER BY action_date DESC");
$history_stmt->bind_param("i", $id);
$history_stmt->execute();
$history = $history_stmt->get_result();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Student Profile</h4>
    <div>
        <a href="index.php" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
        <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-primary-custom"><i class="fas fa-edit me-2"></i>Edit Student</a>
    </div>
</div>

<div class="row">
    <!-- Student Information Card -->
    <div class="col-md-6 mb-4">
        <div class="card-table p-4 mt-0 h-100">
            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($student['name']); ?>&background=4F46E5&color=fff&size=80" class="rounded-circle me-3" alt="<?php echo htmlspecialchars($student['name']); ?>" style="width: 80px; height: 80px; border: 3px solid var(--primary);">
                <div>
                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($student['name']); ?></h5>
                    <p class="text-muted mb-0">Roll No: <strong><?php echo htmlspecialchars($student['roll_no']); ?></strong></p>
                    <?php
                    $status_class = 'bg-secondary';
                    if ($student['status'] == 'Active') $status_class = 'bg-success';
                    elseif ($student['status'] == 'Inactive') $status_class = 'bg-danger';
                    elseif ($student['status'] == 'Graduated') $status_class = 'bg-info';
                    ?>
                    <span class="badge <?php echo $status_class; ?> mt-2"><?php echo htmlspecialchars($student['status']); ?></span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <td class="text-muted fw-bold pb-2" style="width: 35%;">Father's Name:</td>
                            <td class="pb-2"><?php echo htmlspecialchars($student['father_name']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold pb-2">Class:</td>
                            <td class="pb-2">
                                <?php echo htmlspecialchars($student['class_name']); ?>
                                <?php if (!empty($student['section'])): ?>
                                    (Section <?php echo htmlspecialchars($student['section']); ?>)
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold pb-2">Email:</td>
                            <td class="pb-2"><?php echo htmlspecialchars($student['email']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold pb-2">Contact:</td>
                            <td class="pb-2"><?php echo htmlspecialchars($student['contact']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold pb-2">Date Enrolled:</td>
                            <td class="pb-2"><?php echo date('F d, Y', strtotime($student['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold pb-2">Address:</td>
                            <td class="pb-2" style="white-space: pre-line;"><?php echo htmlspecialchars($student['address'] ?? 'Not Provided'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Enrolled Courses Card -->
    <div class="col-md-6 mb-4">
        <div class="card-table p-4 mt-0 h-100">
            <h5 class="fw-bold mb-4"><i class="fas fa-book-open me-2 text-primary"></i>Enrolled Courses</h5>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Course Name</th>
                            <th>Department</th>
                            <th>Credits</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($enrolled_courses && $enrolled_courses->num_rows > 0): ?>
                            <?php while ($course = $enrolled_courses->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                    <td><?php echo htmlspecialchars($course['department']); ?></td>
                                    <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($course['credits']); ?> hrs</span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">This student is not enrolled in any courses yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="text-end mt-3">
                <a href="../allocations/student_create.php" class="btn btn-sm btn-outline-primary"><i class="fas fa-plus me-1"></i> Enroll in a Course</a>
            </div>
        </div>
    </div>
</div>

<!-- Admission & Academic History Timeline Section -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card-table p-4 mt-0">
            <h5 class="fw-bold mb-4"><i class="fas fa-history text-secondary me-2"></i>Admission & Academic History</h5>
            
            <div class="timeline" style="border-left: 2px solid #e5e7eb; padding-left: 24px; margin-left: 10px; position: relative;">
                <?php if ($history && $history->num_rows > 0): ?>
                    <?php while ($h = $history->fetch_assoc()): ?>
                        <div class="timeline-item mb-4" style="position: relative;">
                            <!-- Timeline Dot with Dynamic Color depending on Event Type -->
                            <?php
                            $dot_color = 'var(--primary)';
                            $badge_class = 'bg-primary';
                            $icon = 'fa-info-circle';
                            
                            $type = $h['event_type'];
                            if ($type == 'Registration') {
                                $dot_color = '#10B981'; // Green
                                $badge_class = 'bg-success';
                                $icon = 'fa-id-card';
                            } elseif ($type == 'Status Change') {
                                $dot_color = '#3B82F6'; // Blue
                                $badge_class = 'bg-info text-dark';
                                $icon = 'fa-exchange-alt';
                            } elseif ($type == 'Course Enrollment') {
                                $dot_color = '#8B5CF6'; // Purple
                                $badge_class = 'bg-purple text-white';
                                $icon = 'fa-plus-circle';
                            } elseif ($type == 'Course Removal') {
                                $dot_color = '#EF4444'; // Red
                                $badge_class = 'bg-danger';
                                $icon = 'fa-minus-circle';
                            }
                            ?>
                            
                            <div class="timeline-dot" style="width: 12px; height: 12px; border-radius: 50%; background-color: <?php echo $dot_color; ?>; position: absolute; left: -31px; top: 6px; border: 2px solid #fff; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);"></div>
                            
                            <!-- Timeline Content -->
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <span class="badge <?php echo $badge_class; ?> mb-1">
                                        <i class="fas <?php echo $icon; ?> me-1"></i><?php echo htmlspecialchars($type); ?>
                                    </span>
                                    <p class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($h['description']); ?></p>
                                </div>
                                <span class="text-muted small"><i class="far fa-clock me-1"></i><?php echo date('M d, Y h:i A', strtotime($h['action_date'])); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted py-2 mb-0">No history events recorded yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
