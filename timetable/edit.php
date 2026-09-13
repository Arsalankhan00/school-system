<?php
// Connect to the database and verify the admin is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// Get the schedule's ID from the URL (e.g., edit.php?id=5).
$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Check if the form was submitted to update the schedule
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect all the data from the form
    $id = $_POST['id'];
    $course_id = $_POST['course_id'];
    $teacher_id = $_POST['teacher_id'];
    $day = $_POST['day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $room_no = $_POST['room_no'];

    // --- Validation Step 1: Check Times ---
    if ($start_time >= $end_time) {
        $error = "End time must be after start time.";
    } else {
        // --- Validation Step 2: Teacher Conflict ---
        // Check if the teacher is already teaching another class during this time, ignoring this current schedule row
        // Overlap formula: start_time < proposed_end_time AND end_time > proposed_start_time
        $conflict_query = "SELECT id FROM timetable 
                           WHERE teacher_id = ? AND day = ? AND id != ?
                           AND (start_time < ? AND end_time > ?)";
        $stmt_conflict = $conn->prepare($conflict_query);
        $stmt_conflict->bind_param("isiss", $teacher_id, $day, $id, $end_time, $start_time);
        $stmt_conflict->execute();
        
        if ($stmt_conflict->get_result()->num_rows > 0) {
            $error = "Time conflict! Teacher is already scheduled for another class at this time.";
        } else {
            // --- Validation Step 3: Room Conflict ---
            // Check if the selected room is already booked by another class, ignoring this current schedule row
            $room_conflict = "SELECT id FROM timetable 
                              WHERE room_no = ? AND day = ? AND id != ? 
                              AND (start_time < ? AND end_time > ?)";
            $stmt_room = $conn->prepare($room_conflict);
            $stmt_room->bind_param("ssiss", $room_no, $day, $id, $end_time, $start_time);
            $stmt_room->execute();
            
            if ($stmt_room->get_result()->num_rows > 0) {
                $error = "Room conflict! Room is already booked at this time.";
            } else {
                // If it passes all checks, UPDATE the schedule!
                $stmt = $conn->prepare("UPDATE timetable SET course_id=?, teacher_id=?, day=?, start_time=?, end_time=?, room_no=? WHERE id=?");
                $stmt->bind_param("iissssi", $course_id, $teacher_id, $day, $start_time, $end_time, $room_no, $id);
                
                if ($stmt->execute()) {
                    $success = "Schedule updated successfully!";
                } else {
                    $error = "Error updating schedule: " . $conn->error;
                }
            }
        }
    }
}

// Fetch the single schedule row to pre-fill the form
$stmt = $conn->prepare("SELECT * FROM timetable WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$schedule = $stmt->get_result()->fetch_assoc();

// If the database didn't find the schedule with that ID, show error
if (!$schedule) {
    echo "<div class='alert alert-danger'>Schedule not found.</div>";
    require_once '../../includes/footer.php';
    exit();
}

// Fetch all courses and teachers to populate the dropdown menus in the form
$courses = $conn->query("SELECT id, course_name, course_code FROM courses ORDER BY course_name ASC");
$teachers = $conn->query("SELECT id, name FROM teachers ORDER BY name ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Edit Schedule</h4>
    <div>
        <a href="view.php?id=<?php echo $schedule['id']; ?>" class="btn btn-outline-info me-2"><i class="fas fa-eye me-2"></i>View Schedule</a>
        <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Timetable</a>
    </div>
</div>

<div class="card-table p-4 mt-0">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="edit.php?id=<?php echo $id; ?>" method="POST">
        <input type="hidden" name="id" value="<?php echo $schedule['id']; ?>">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Course</label>
                <select name="course_id" class="form-select" required>
                    <option value="">-- Select Course --</option>
                    <?php while ($row = $courses->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>" <?php echo ($schedule['course_id'] == $row['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['course_name']) . " (" . htmlspecialchars($row['course_code']) . ")"; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Teacher</label>
                <select name="teacher_id" class="form-select" required>
                    <option value="">-- Select Teacher --</option>
                    <?php while ($row = $teachers->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>" <?php echo ($schedule['teacher_id'] == $row['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Day</label>
                <select name="day" class="form-select" required>
                    <option value="Monday" <?php echo ($schedule['day'] == 'Monday') ? 'selected' : ''; ?>>Monday</option>
                    <option value="Tuesday" <?php echo ($schedule['day'] == 'Tuesday') ? 'selected' : ''; ?>>Tuesday</option>
                    <option value="Wednesday" <?php echo ($schedule['day'] == 'Wednesday') ? 'selected' : ''; ?>>Wednesday</option>
                    <option value="Thursday" <?php echo ($schedule['day'] == 'Thursday') ? 'selected' : ''; ?>>Thursday</option>
                    <option value="Friday" <?php echo ($schedule['day'] == 'Friday') ? 'selected' : ''; ?>>Friday</option>
                    <option value="Saturday" <?php echo ($schedule['day'] == 'Saturday') ? 'selected' : ''; ?>>Saturday</option>
                    <option value="Sunday" <?php echo ($schedule['day'] == 'Sunday') ? 'selected' : ''; ?>>Sunday</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Room Number</label>
                <input type="text" name="room_no" class="form-control" value="<?php echo htmlspecialchars($schedule['room_no']); ?>" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label">Start Time</label>
                <input type="time" name="start_time" class="form-control" value="<?php echo htmlspecialchars(date('H:i', strtotime($schedule['start_time']))); ?>" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label">End Time</label>
                <input type="time" name="end_time" class="form-control" value="<?php echo htmlspecialchars(date('H:i', strtotime($schedule['end_time']))); ?>" required>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-2"></i>Update Schedule</button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
