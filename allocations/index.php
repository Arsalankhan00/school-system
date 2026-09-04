<?php
// Connect to the database and check if logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// --- Handle Search Functionality ---
$search = $_GET['search'] ?? '';
$where_clause_teacher = "";
$where_clause_student = "";
if (!empty($search)) {
    $search_safe = $conn->real_escape_string($search);
    $where_clause_teacher = "WHERE teachers.name LIKE '%$search_safe%' OR courses.course_name LIKE '%$search_safe%' OR courses.course_code LIKE '%$search_safe%'";
    $where_clause_student = "WHERE students.name LIKE '%$search_safe%' OR students.roll_no LIKE '%$search_safe%' OR courses.course_name LIKE '%$search_safe%' OR courses.course_code LIKE '%$search_safe%'";
}

// Fetch Teacher Allocations
$query_teachers = "SELECT teacher_courses.id, teachers.name as teacher_name, courses.course_name, courses.course_code 
                  FROM teacher_courses 
                  JOIN teachers ON teacher_courses.teacher_id = teachers.id 
                  JOIN courses ON teacher_courses.course_id = courses.id 
                  $where_clause_teacher 
                  ORDER BY teachers.name ASC";
$allocations_teachers = $conn->query($query_teachers);

// Fetch Student Allocations
$query_students = "SELECT student_courses.id, students.name as student_name, students.roll_no, courses.course_name, courses.course_code 
                  FROM student_courses 
                  JOIN students ON student_courses.student_id = students.id 
                  JOIN courses ON student_courses.course_id = courses.id 
                  $where_clause_student 
                  ORDER BY students.name ASC";
$allocations_students = $conn->query($query_students);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Course Allocations</h4>
    <div>
        <a href="create.php" class="btn btn-primary-custom me-2"><i class="fas fa-chalkboard-teacher me-2"></i>Assign to Teacher</a>
        <a href="student_create.php" class="btn btn-outline-primary"><i class="fas fa-user-graduate me-2"></i>Assign to Student</a>
    </div>
</div>

<div class="card-table p-4 mt-0">
    <div class="row mb-4">
        <div class="col-md-6">
            <form action="index.php" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search allocations..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>

    <!-- Nav Tabs -->
    <ul class="nav nav-tabs mb-4" id="allocationTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="teachers-tab" data-bs-toggle="tab" data-bs-target="#teachers" type="button" role="tab">Teacher Allocations</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button" role="tab">Student Enrollment</button>
        </li>
    </ul>

    <div class="tab-content" id="allocationTabsContent">
        <!-- Teacher Allocations -->
        <div class="tab-pane fade show active" id="teachers" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Teacher Name</th>
                            <th>Assigned Course</th>
                            <th>Course Code</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($allocations_teachers && $allocations_teachers->num_rows > 0): ?>
                            <?php while ($row = $allocations_teachers->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['teacher_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['course_code']); ?></span></td>
                                    <td>
                                        <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to remove this assignment?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted">No teacher allocations found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Student Allocations -->
        <div class="tab-pane fade" id="students" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student Name (Roll No)</th>
                            <th>Enrolled Course</th>
                            <th>Course Code</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($allocations_students && $allocations_students->num_rows > 0): ?>
                            <?php while ($row = $allocations_students->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['student_name']) . " (" . htmlspecialchars($row['roll_no']) . ")"; ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['course_code']); ?></span></td>
                                    <td>
                                        <a href="student_delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to remove this enrollment?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted">No student enrollments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
