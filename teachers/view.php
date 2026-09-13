<?php
// Connect to the database and verify the admin is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// Get the teacher's ID from the URL. If not provided, default to 0.
$id = $_GET['id'] ?? 0;

$success_msg = '';
$error_msg = '';

// --- Handle Inline Actions (Assign/Remove Course) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $course_id = $_POST['course_id'] ?? 0;
    
    if ($action == 'quick_assign' && $course_id > 0) {
        $chk = $conn->prepare("SELECT id FROM teacher_courses WHERE teacher_id = ? AND course_id = ?");
        $chk->bind_param("ii", $id, $course_id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error_msg = "This course is already assigned to this teacher.";
        } else {
            $stmt_ins = $conn->prepare("INSERT INTO teacher_courses (teacher_id, course_id) VALUES (?, ?)");
            $stmt_ins->bind_param("ii", $id, $course_id);
            if ($stmt_ins->execute()) {
                $success_msg = "Course assigned successfully!";
            } else {
                $error_msg = "Error assigning course: " . $conn->error;
            }
        }
    } elseif ($action == 'quick_remove' && $course_id > 0) {
        $stmt_del = $conn->prepare("DELETE FROM teacher_courses WHERE teacher_id = ? AND course_id = ?");
        $stmt_del->bind_param("ii", $id, $course_id);
        if ($stmt_del->execute()) {
            $success_msg = "Course assignment removed successfully!";
        } else {
            $error_msg = "Error removing course assignment: " . $conn->error;
        }
    }
}

// Fetch the teacher's details
$stmt = $conn->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

// If the teacher doesn't exist, show an error and exit
if (!$teacher) {
    echo "<div class='alert alert-danger'>Teacher not found.</div>";
    require_once '../../includes/footer.php';
    exit();
}

// Fetch courses assigned to this teacher
$courses_stmt = $conn->prepare("
    SELECT c.id, c.course_name, c.course_code, c.credits, c.department 
    FROM teacher_courses tc 
    JOIN courses c ON tc.course_id = c.id 
    WHERE tc.teacher_id = ? 
    ORDER BY c.course_name ASC
");
$courses_stmt->bind_param("i", $id);
$courses_stmt->execute();
$assigned_courses = $courses_stmt->get_result();

// Calculate total load (credits)
$total_credits = 0;
$assigned_list = [];
if ($assigned_courses) {
    while ($row = $assigned_courses->fetch_assoc()) {
        $total_credits += $row['credits'];
        $assigned_list[] = $row;
    }
}

// Fetch unassigned courses for the quick assign dropdown
$unassigned_courses = $conn->query("
    SELECT id, course_name, course_code 
    FROM courses 
    WHERE id NOT IN (SELECT course_id FROM teacher_courses WHERE teacher_id = $id) 
    ORDER BY course_name ASC
");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Teacher Profile</h4>
    <div>
        <a href="index.php" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left me-2"></i>Back to Teachers</a>
        <a href="students.php?id=<?php echo $teacher['id']; ?>" class="btn btn-outline-info me-2"><i class="fas fa-users me-2"></i>View Assigned Students</a>
        <a href="edit.php?id=<?php echo $teacher['id']; ?>" class="btn btn-primary-custom"><i class="fas fa-edit me-2"></i>Edit Teacher</a>
    </div>
</div>

<?php if ($success_msg): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo $success_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if ($error_msg): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Teacher Information Card -->
    <div class="col-md-6 mb-4">
        <div class="card-table p-4 mt-0 h-100">
            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($teacher['name']); ?>&background=10B981&color=fff&size=80" class="rounded-circle me-3" alt="<?php echo htmlspecialchars($teacher['name']); ?>" style="width: 80px; height: 80px; border: 3px solid var(--secondary);">
                <div>
                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($teacher['name']); ?></h5>
                    <p class="text-muted mb-2">Department: <span class="badge bg-secondary"><?php echo htmlspecialchars($teacher['department'] ?? 'Not Specified'); ?></span></p>
                    <?php
                    $status = $teacher['status'] ?? 'Active';
                    $status_class = 'bg-secondary';
                    if ($status == 'Active') $status_class = 'bg-success';
                    elseif ($status == 'On Leave') $status_class = 'bg-warning text-dark';
                    elseif ($status == 'Inactive') $status_class = 'bg-danger';
                    ?>
                    <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted fw-bold pb-2" style="width: 35%;">Teacher ID:</td>
                            <td class="pb-2"><?php echo htmlspecialchars($teacher['id']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold pb-2">Full Name:</td>
                            <td class="pb-2"><?php echo htmlspecialchars($teacher['name']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold pb-2">Email Address:</td>
                            <td class="pb-2"><?php echo htmlspecialchars($teacher['email']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold pb-2">Contact Number:</td>
                            <td class="pb-2"><?php echo htmlspecialchars($teacher['contact']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold pb-2">Date Joined:</td>
                            <td class="pb-2"><?php echo date('F d, Y', strtotime($teacher['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold pb-2">Teaching Load:</td>
                            <td class="pb-2">
                                <strong><?php echo $total_credits; ?> Credits</strong>
                                <?php
                                if ($total_credits < 6) {
                                    echo '<span class="badge bg-warning text-dark ms-2" title="Less than 6 credits"><i class="fas fa-exclamation-triangle me-1"></i>Underloaded</span>';
                                } elseif ($total_credits > 15) {
                                    echo '<span class="badge bg-danger ms-2" title="More than 15 credits"><i class="fas fa-exclamation-circle me-1"></i>Overloaded</span>';
                                } else {
                                    echo '<span class="badge bg-success ms-2" title="6-15 credits"><i class="fas fa-check-circle me-1"></i>Normal Load</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Assigned Courses Card (Teaching Load Management) -->
    <div class="col-md-6 mb-4">
        <div class="card-table p-4 mt-0 h-100 d-flex flex-column justify-content-between">
            <div>
                <h5 class="fw-bold mb-4"><i class="fas fa-chalkboard me-2 text-success"></i>Assigned Courses</h5>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Course Name</th>
                                <th>Department</th>
                                <th>Credits</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($assigned_list)): ?>
                                <?php foreach ($assigned_list as $course): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars($course['department']); ?></td>
                                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($course['credits']); ?> hrs</span></td>
                                        <td class="text-center">
                                            <form action="view.php?id=<?php echo $id; ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this course assignment?');">
                                                <input type="hidden" name="action" value="quick_remove">
                                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove course"><i class="fas fa-trash-alt"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No courses assigned to this teacher yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Quick Assign Form (Teacher Load management option) -->
            <div class="border-top pt-4 mt-3">
                <h6 class="fw-bold mb-3"><i class="fas fa-plus-circle me-2 text-success"></i>Assign Course (Load Management)</h6>
                <form action="view.php?id=<?php echo $id; ?>" method="POST" class="row g-2 align-items-center">
                    <input type="hidden" name="action" value="quick_assign">
                    <div class="col-8">
                        <select name="course_id" class="form-select form-select-sm" required>
                            <option value="">-- Select Course --</option>
                            <?php if ($unassigned_courses && $unassigned_courses->num_rows > 0): ?>
                                <?php while ($uc = $unassigned_courses->fetch_assoc()): ?>
                                    <option value="<?php echo $uc['id']; ?>"><?php echo htmlspecialchars($uc['course_name']) . " (" . htmlspecialchars($uc['course_code']) . ")"; ?></option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-sm btn-success w-100"><i class="fas fa-plus me-1"></i>Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
