<?php
require_once '../../includes/db.php';
require_once '../../includes/header.php';

$error = '';
$success = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_name = $_POST['class_name'];
    $fee_type = $_POST['fee_type'];
    $amount = $_POST['amount'];

    if (empty($class_name) || empty($fee_type) || empty($amount)) {
        $error = "Please fill all fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO fee_structure (class_name, fee_type, amount) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE amount = ?");
        $stmt->bind_param("ssdd", $class_name, $fee_type, $amount, $amount);
        
        if ($stmt->execute()) {
            $success = "Fee structure updated successfully!";
        } else {
            $error = "Error updating fee structure: " . $conn->error;
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM fee_structure WHERE id = $id");
    header("Location: structure.php?success=Deleted successfully");
    exit();
}

// Fetch Fee Structures
$structures = $conn->query("SELECT * FROM fee_structure ORDER BY class_name ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Fee Structure Setup</h4>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Fees</a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card-table p-4">
            <h5 class="mb-4">Add/Update Fee</h5>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
            <?php if ($success || isset($_GET['success'])): ?><div class="alert alert-success"><?php echo $success ?: $_GET['success']; ?></div><?php endif; ?>

            <form action="structure.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Class Name</label>
                    <input type="text" name="class_name" class="form-control" placeholder="e.g. Class 10" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fee Type</label>
                    <select name="fee_type" class="form-select" required>
                        <option value="Tuition Fee">Tuition Fee</option>
                        <option value="Admission Fee">Admission Fee</option>
                        <option value="Exam Fee">Exam Fee</option>
                        <option value="Transport Fee">Transport Fee</option>
                        <option value="Activity Fee">Activity Fee</option>
                        <option value="Lab Fee">Lab Fee</option>
                        <option value="Library Fee">Library Fee</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label">Fee Amount (PKR)</label>
                    <input type="number" name="amount" class="form-control" placeholder="e.g. 5000" required>
                </div>
                <button type="submit" class="btn btn-primary-custom w-100"><i class="fas fa-save me-2"></i>Save Structure</button>
            </form>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card-table p-4">
            <h5 class="mb-4">Current Fee Structures</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Class Name</th>
                            <th>Fee Type</th>
                            <th>Monthly Amount</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($structures && $structures->num_rows > 0): ?>
                            <?php while ($row = $structures->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['class_name']); ?></strong></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['fee_type']); ?></span></td>
                                    <td>RS <?php echo number_format($row['amount'], 0); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['updated_at'])); ?></td>
                                    <td>
                                        <a href="structure.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted">No fee structures defined yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
