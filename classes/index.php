<?php
// Connect to the database
require_once '../../includes/db.php';
// Load the header
require_once '../../includes/header.php';

// Handle Search
$search = $_GET['search'] ?? '';
$where_clause = "";

if (!empty($search)) {
    $search_safe = $conn->real_escape_string($search);
    $where_clause = "WHERE c.class_name LIKE '%$search_safe%' OR c.section LIKE '%$search_safe%'";
}

// Fetch classes along with the count of enrolled students
$query = "SELECT c.*, COUNT(s.id) as total_students 
          FROM classes c 
          LEFT JOIN students s ON c.class_name = s.class_name AND (c.section = s.section OR (c.section IS NULL AND (s.section IS NULL OR s.section = '')))
          $where_clause 
          GROUP BY c.id 
          ORDER BY c.class_name ASC, c.section ASC";

$classes = $conn->query($query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Classes Management</h4>
    <a href="create.php" class="btn btn-primary-custom"><i class="fas fa-plus me-2"></i>Add Class</a>
</div>

<div class="card-table p-4 mt-0">
    <div class="row mb-3">
        <div class="col-md-6">
            <form action="index.php" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search by class name or section..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Class Name</th>
                    <th>Section</th>
                    <th>Enrolled Students</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($classes && $classes->num_rows > 0): ?>
                    <?php while ($row = $classes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['class_name']); ?></strong></td>
                            <td>
                                <?php if (!empty($row['section'])): ?>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($row['section']); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['total_students']); ?> Students</span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-success" title="View Details"><i class="fas fa-eye"></i></a>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Class"><i class="fas fa-edit"></i></a>
                                    <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this class?');" title="Delete Class"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted">No classes found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
