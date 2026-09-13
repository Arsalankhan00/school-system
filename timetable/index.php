<?php
// Connect to the database and check if the user is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// --- Fetch Timetable Data using Multiple JOINs ---
// The timetable table stores course_id and teacher_id.
// We JOIN the 'courses' table to get the course_name and course_code.
// We JOIN the 'teachers' table to get the teacher's name.
// ORDER BY FIELD is a neat SQL trick to sort the days of the week in proper order instead of alphabetically!
$query = "SELECT t.*, c.course_name, c.course_code, tr.name as teacher_name 
          FROM timetable t 
          JOIN courses c ON t.course_id = c.id 
          JOIN teachers tr ON t.teacher_id = tr.id 
          ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), start_time ASC";

// Execute the query to get all schedules
$timetable = $conn->query($query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Timetable & Scheduling</h4>
    <a href="create.php" class="btn btn-primary-custom"><i class="fas fa-plus me-2"></i>Add Schedule</a>
</div>

<div class="card-table p-4 mt-0">
    <div class="table-responsive">
        <table class="table table-hover table-bordered text-center">
            <thead class="table-light">
                <tr>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Course</th>
                    <th>Teacher</th>
                    <th>Room No</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($timetable && $timetable->num_rows > 0): ?>
                    <?php 
                    $current_day = '';
                    while ($row = $timetable->fetch_assoc()): 
                        // To show Day only once per group (optional formatting, keeping simple for now)
                    ?>
                        <tr>
                            <td><strong><?php echo $row['day']; ?></strong></td>
                            <td><?php echo date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time'])); ?></td>
                            <td><?php echo htmlspecialchars($row['course_name']) . " (" . htmlspecialchars($row['course_code']) . ")"; ?></td>
                            <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['room_no']); ?></td>
                            <td>
                                <!-- Action Buttons: View, Edit, Delete -->
                                <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-success" title="View Details"><i class="fas fa-eye"></i></a>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Schedule"><i class="fas fa-edit"></i></a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this schedule?');" title="Delete Schedule"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted">No schedules found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
