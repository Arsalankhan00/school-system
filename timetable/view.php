<?php
// Connect to the database and verify the admin is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// Get the schedule's ID from the URL. If not provided, default to 0.
$id = $_GET['id'] ?? 0;

// Fetch schedule details using JOINs to get course and teacher names
$query = "SELECT t.*, c.course_name, c.course_code, tr.name as teacher_name, tr.id as teacher_db_id, c.id as course_db_id
          FROM timetable t 
          JOIN courses c ON t.course_id = c.id 
          JOIN teachers tr ON t.teacher_id = tr.id 
          WHERE t.id = ?";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$schedule = $stmt->get_result()->fetch_assoc();

// If the schedule doesn't exist, show an error and exit
if (!$schedule) {
    echo "<div class='alert alert-danger'>Schedule not found.</div>";
    require_once '../../includes/footer.php';
    exit();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Schedule Details</h4>
    <div>
        <a href="index.php" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left me-2"></i>Back to Timetable</a>
        <a href="edit.php?id=<?php echo $schedule['id']; ?>" class="btn btn-primary-custom"><i class="fas fa-edit me-2"></i>Edit Schedule</a>
    </div>
</div>

<div class="row">
    <!-- Schedule Info Card -->
    <div class="col-md-6 mb-4 mx-auto">
        <div class="card-table p-4 mt-0">
            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                <div class="d-inline-flex align-items-center justify-content-center bg-gradient-primary text-white rounded-circle me-3" style="width: 60px; height: 60px; font-size: 1.6rem; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($schedule['day']); ?></h5>
                    <p class="text-muted mb-0"><?php echo date('h:i A', strtotime($schedule['start_time'])) . ' - ' . date('h:i A', strtotime($schedule['end_time'])); ?></p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <td class="text-muted fw-bold pb-2" style="width: 35%;">Course:</td>
                            <td class="pb-2">
                                <a href="../courses/view.php?id=<?php echo $schedule['course_db_id']; ?>" class="text-decoration-none fw-bold text-dark">
                                    <?php echo htmlspecialchars($schedule['course_name']); ?> (<?php echo htmlspecialchars($schedule['course_code']); ?>)
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold pb-2">Teacher:</td>
                            <td class="pb-2">
                                <a href="../teachers/view.php?id=<?php echo $schedule['teacher_db_id']; ?>" class="text-decoration-none fw-bold text-dark">
                                    <?php echo htmlspecialchars($schedule['teacher_name']); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold pb-2">Room Number:</td>
                            <td class="pb-2"><span class="badge bg-info text-dark"><?php echo htmlspecialchars($schedule['room_no']); ?></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
