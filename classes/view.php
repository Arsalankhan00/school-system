<?php
// Connect to the database and verify the admin is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// Get the class ID from URL
$id = $_GET['id'] ?? 0;

// Fetch class details
$stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$class_data = $stmt->get_result()->fetch_assoc();

if (!$class_data) {
    echo "<div class='alert alert-danger'>Class not found.</div>";
    require_once '../../includes/footer.php';
    exit();
}

// Fetch students enrolled in this class
$class_name = $class_data['class_name'];
$section = $class_data['section'];

if (empty($section)) {
    $students_stmt = $conn->prepare("SELECT * FROM students WHERE class_name = ? AND (section IS NULL OR section = '') ORDER BY name ASC");
    $students_stmt->bind_param("s", $class_name);
} else {
    $students_stmt = $conn->prepare("SELECT * FROM students WHERE class_name = ? AND section = ? ORDER BY name ASC");
    $students_stmt->bind_param("ss", $class_name, $section);
}
$students_stmt->execute();
$students = $students_stmt->get_result();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Class Details: <?php echo htmlspecialchars($class_name) . (!empty($section) ? " (" . htmlspecialchars($section) . ")" : ""); ?></h4>
    <div>
        <a href="index.php" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
        <a href="edit.php?id=<?php echo $class_data['id']; ?>" class="btn btn-primary-custom"><i class="fas fa-edit me-2"></i>Edit Class</a>
    </div>
</div>

<div class="row">
    <!-- Class Info Card -->
    <div class="col-md-4 mb-4">
        <div class="card-table p-4 mt-0 h-100">
            <h5 class="fw-bold mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Class Information</h5>
            <hr>
            <div class="mb-3">
                <span class="text-muted d-block">Class Name:</span>
                <span class="fs-5 fw-bold"><?php echo htmlspecialchars($class_name); ?></span>
            </div>
            <div class="mb-3">
                <span class="text-muted d-block">Section:</span>
                <span class="fs-5 fw-bold">
                    <?php echo !empty($section) ? htmlspecialchars($section) : '<span class="text-muted">None</span>'; ?>
                </span>
            </div>
            <div class="mb-3">
                <span class="text-muted d-block">Created At:</span>
                <span><?php echo date('F d, Y', strtotime($class_data['created_at'])); ?></span>
            </div>
            <div class="mb-3">
                <span class="text-muted d-block">Total Enrolled:</span>
                <span class="badge bg-info text-dark fs-6"><?php echo $students->num_rows; ?> Students</span>
            </div>
        </div>
    </div>

    <!-- Enrolled Students List Card -->
    <div class="col-md-8 mb-4">
        <div class="card-table p-4 mt-0 h-100">
            <h5 class="fw-bold mb-4"><i class="fas fa-users text-success me-2"></i>Enrolled Students</h5>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($students && $students->num_rows > 0): ?>
                            <?php while ($s = $students->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($s['roll_no']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($s['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($s['email']); ?></td>
                                    <td><?php echo htmlspecialchars($s['contact']); ?></td>
                                    <td>
                                        <a href="../students/view.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-success" title="View Profile"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No students are currently enrolled in this class.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
