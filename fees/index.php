<?php
// Connect to the database and check if the user is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// --- Handle Search Functionality ---
$search = $_GET['search'] ?? '';
$where_clause = "";
if (!empty($search)) {
    $search_safe = $conn->real_escape_string($search);
    // Search by student name, roll number, or the specific fee receipt number
    $where_clause = "WHERE students.name LIKE '%$search_safe%' OR students.roll_no LIKE '%$search_safe%' OR fees.receipt_no LIKE '%$search_safe%'";
}

// --- Fetch Data using a JOIN Query ---
// We use a JOIN because the 'fees' table only has 'student_id'. 
// We need to join it with the 'students' table to get the student's actual name and roll number.
$query = "SELECT fees.*, students.name as student_name, students.roll_no 
          FROM fees 
          JOIN students ON fees.student_id = students.id 
          $where_clause 
          ORDER BY payment_date DESC"; // Show the newest payments first
$fees = $conn->query($query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Fee Management</h4>
    <div>
        <a href="structure.php" class="btn btn-outline-secondary me-2"><i class="fas fa-list-ul me-2"></i>Fee Structure</a>
        <a href="report.php" class="btn btn-outline-info me-2"><i class="fas fa-chart-line me-2"></i>Financial Report</a>
        <a href="create.php" class="btn btn-primary-custom"><i class="fas fa-plus me-2"></i>Record Payment</a>
    </div>
</div>

<div class="card-table p-4 mt-0">
    <div class="row mb-3">
        <div class="col-md-6">
            <form action="index.php" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search by student name, roll no, or receipt..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Receipt No</th>
                    <th>Student Name (Roll No)</th>
                    <th>Amount Paid</th>
                    <th>Payment Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($fees && $fees->num_rows > 0): ?>
                    <?php while ($row = $fees->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['receipt_no']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['student_name']) . " (" . htmlspecialchars($row['roll_no']) . ")"; ?></td>
                            <td>RS <?php echo number_format($row['amount_paid'], 0); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                            <td>
                                <?php if ($row['status'] == 'Paid'): ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php elseif ($row['status'] == 'Pending'): ?>
                                    <span class="badge bg-danger">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Partial</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-success" title="View Details"><i class="fas fa-eye"></i></a>
                                    <a href="receipt.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-info" target="_blank" title="Print Receipt"><i class="fas fa-print"></i></a>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this record?');"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted">No fee records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
