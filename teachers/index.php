<?php
// Include the database connection and the header (which checks if the user is logged in)
require_once '../../includes/db.php';
require_once '../../includes/header.php';

$success_msg = '';
$error_msg = '';

// --- Handle Status Toggle ---
if (isset($_GET['change_status']) && isset($_GET['id'])) {
    $teacher_id = intval($_GET['id']);
    $new_status = $_GET['change_status'];
    
    // Validate status values
    if (in_array($new_status, ['Active', 'On Leave', 'Inactive'])) {
        $stmt_status = $conn->prepare("UPDATE teachers SET status = ? WHERE id = ?");
        $stmt_status->bind_param("si", $new_status, $teacher_id);
        if ($stmt_status->execute()) {
            $success_msg = "Teacher status updated to " . htmlspecialchars($new_status) . " successfully!";
        } else {
            $error_msg = "Error updating status: " . $conn->error;
        }
    }
}

// --- Handle Search Functionality ---
// Get the search term from the URL (e.g. index.php?search=Ahmed)
// If there is no search term, default to an empty string ('')
$search = $_GET['search'] ?? '';
$where_clause = "";

// If the user typed something into the search box
if (!empty($search)) {
    // Clean the search text to prevent SQL injection attacks
    $search_safe = $conn->real_escape_string($search);
    
    // Build the WHERE clause to find teachers by their name OR their department
    $where_clause = "WHERE name LIKE '%$search_safe%' OR department LIKE '%$search_safe%'";
}

// --- Fetch Data ---
// Query the database to get all teachers, applying the search filter if one exists.
// 'ORDER BY created_at DESC' sorts them so the newest teachers appear first.
$teachers = $conn->query("SELECT * FROM teachers $where_clause ORDER BY created_at DESC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Teachers Management</h4>
    <a href="create.php" class="btn btn-primary-custom"><i class="fas fa-plus me-2"></i>Add Teacher</a>
</div>

<div class="card-table p-4 mt-0">
    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-3">
        <div class="col-md-6">
            <!-- Search Form -->
            <form action="index.php" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search by name or department..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Teacher</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Check if the query returned any results
                if ($teachers && $teachers->num_rows > 0): 
                ?>
                    <?php 
                    // Loop through each teacher record from the database one by one
                    while ($row = $teachers->fetch_assoc()): 
                    ?>
                        <tr>
                            <!-- Display the teacher's details safely using htmlspecialchars() -->
                            <td>
                                <strong class="text-dark d-block mb-1"><?php echo htmlspecialchars($row['name']); ?></strong>
                                <small class="text-muted"><i class="fas fa-phone me-1 text-secondary"></i><?php echo htmlspecialchars($row['contact']); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($row['department'] ?? 'General'); ?></span>
                            </td>
                            <td>
                                <?php
                                $status = $row['status'] ?? 'Active';
                                $status_class = 'bg-secondary';
                                if ($status == 'Active') $status_class = 'bg-success';
                                elseif ($status == 'On Leave') $status_class = 'bg-warning text-dark';
                                elseif ($status == 'Inactive') $status_class = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                            </td>
                            <td class="text-center">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Manage
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item text-success" href="view.php?id=<?php echo $row['id']; ?>"><i class="fas fa-eye me-2"></i>View Profile</a></li>
                                        <li><a class="dropdown-item text-info" href="students.php?id=<?php echo $row['id']; ?>"><i class="fas fa-users me-2"></i>View Students</a></li>
                                        <li><a class="dropdown-item text-primary" href="edit.php?id=<?php echo $row['id']; ?>"><i class="fas fa-edit me-2"></i>Edit Details</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <!-- Quick Status Change Actions -->
                                        <?php if ($status != 'Active'): ?>
                                            <li><a class="dropdown-item" href="index.php?change_status=Active&id=<?php echo $row['id']; ?>"><i class="fas fa-check-circle text-success me-2"></i>Set Active</a></li>
                                        <?php endif; ?>
                                        <?php if ($status != 'On Leave'): ?>
                                            <li><a class="dropdown-item" href="index.php?change_status=On Leave&id=<?php echo $row['id']; ?>"><i class="fas fa-plane-departure text-warning me-2"></i>Set On Leave</a></li>
                                        <?php endif; ?>
                                        <?php if ($status != 'Inactive'): ?>
                                            <li><a class="dropdown-item" href="index.php?change_status=Inactive&id=<?php echo $row['id']; ?>"><i class="fas fa-times-circle text-danger me-2"></i>Set Inactive</a></li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this teacher?');"><i class="fas fa-trash-alt me-2"></i>Delete</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php 
                    endwhile; // End of the while loop
                    ?>
                <?php else: ?>
                    <!-- Message displayed if the database has no teachers -->
                    <tr><td colspan="4" class="text-center text-muted">No teachers found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
// Include the footer to close HTML tags and load scripts
require_once '../../includes/footer.php'; 
?>
