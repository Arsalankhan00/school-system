<?php
// Connect to the database and ensure the user is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// --- Handle Search Functionality ---
// Grab the search text from the URL (e.g. index.php?search=Math)
$search = $_GET['search'] ?? '';
$where_clause = "";

// If the search text is not empty, build a WHERE filter
if (!empty($search)) {
    // Clean the search text to prevent SQL injection
    $search_safe = $conn->real_escape_string($search);
    // Find courses matching the name, code, or department
    $where_clause = "WHERE course_name LIKE '%$search_safe%' OR course_code LIKE '%$search_safe%' OR department LIKE '%$search_safe%'";
}

// --- Fetch Data ---
// Query all courses, apply the search filter if there is one, and sort by newest first
$courses = $conn->query("SELECT * FROM courses $where_clause ORDER BY created_at DESC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Courses Management</h4>
    <a href="create.php" class="btn btn-primary-custom"><i class="fas fa-plus me-2"></i>Add Course</a>
</div>

<div class="card-table p-4 mt-0">
    <div class="row mb-3">
        <div class="col-md-6">
            <!-- Search Form -->
            <form action="index.php" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search by name, code, or department..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Department</th>
                    <th>Credit Hours</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Check if any courses were found in the database
                if ($courses && $courses->num_rows > 0): 
                ?>
                    <?php 
                    // Loop through each course record
                    while ($row = $courses->fetch_assoc()): 
                    ?>
                        <tr>
                            <!-- Display the course data securely -->
                            <td><strong><?php echo htmlspecialchars($row['course_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                            <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['credits'] ?? 0); ?> hrs</span></td>
                            <td>
                                <!-- Action Buttons for View, Editing, and Deleting -->
                                <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-success" title="View Details"><i class="fas fa-eye"></i></a>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Course"><i class="fas fa-edit"></i></a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this course?');" title="Delete Course"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php 
                    endwhile; 
                    ?>
                <?php else: ?>
                    <!-- Message if no courses exist -->
                    <tr><td colspan="5" class="text-center text-muted">No courses found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
// Include the footer 
require_once '../../includes/footer.php'; 
?>
