<?php
// Connect to the database and check if the user is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Check if the form was submitted to update a payment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $student_id = $_POST['student_id'];
    $amount_paid = $_POST['amount_paid'];
    $payment_date = $_POST['payment_date'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];

    // Update the fee record
    $stmt = $conn->prepare("UPDATE fees SET student_id=?, amount_paid=?, payment_date=?, status=?, remarks=? WHERE id=?");
    $stmt->bind_param("idsssi", $student_id, $amount_paid, $payment_date, $status, $remarks, $id);
    
    if ($stmt->execute()) {
        $success = "Fee record updated successfully!";
    } else {
        $error = "Error updating fee record: " . $conn->error;
    }
}

// Fetch the existing fee record
$stmt = $conn->prepare("SELECT * FROM fees WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$fee = $stmt->get_result()->fetch_assoc();

if (!$fee) {
    echo "<div class='alert alert-danger'>Fee record not found.</div>";
    require_once '../../includes/footer.php';
    exit();
}

// Fetch a list of all students for the dropdown
$students = $conn->query("SELECT id, name, roll_no FROM students ORDER BY name ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Edit Fee Payment</h4>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
</div>

<div class="card-table p-4 mt-0">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="edit.php?id=<?php echo $id; ?>" method="POST">
        <input type="hidden" name="id" value="<?php echo $fee['id']; ?>">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Select Student</label>
                <select name="student_id" class="form-select" required>
                    <option value="">-- Select Student --</option>
                    <?php while ($row = $students->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>" <?php echo ($row['id'] == $fee['student_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['name']) . " (" . htmlspecialchars($row['roll_no']) . ")"; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Amount Paid ($)</label>
                <input type="number" step="0.01" name="amount_paid" class="form-control" value="<?php echo $fee['amount_paid']; ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Payment Date</label>
                <input type="date" name="payment_date" class="form-control" value="<?php echo $fee['payment_date']; ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="Paid" <?php echo ($fee['status'] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                    <option value="Pending" <?php echo ($fee['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Partial" <?php echo ($fee['status'] == 'Partial') ? 'selected' : ''; ?>>Partial</option>
                </select>
            </div>
            <div class="col-md-12 mb-4">
                <label class="form-label">Remarks / Description</label>
                <input type="text" name="remarks" class="form-control" value="<?php echo htmlspecialchars($fee['remarks']); ?>">
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-2"></i>Update Payment</button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
