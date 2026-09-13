<?php
// Connect to the database and check if the user is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

$error = '';
$success = '';

// Check if the form was submitted to record a payment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $amount_paid = $_POST['amount_paid'];
    $payment_date = $_POST['payment_date'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];
    
    // Generate a unique receipt number automatically using the current timestamp and a random number
    // Example: REC-170045612345
    $receipt_no = "REC-" . time() . rand(10, 99);

    // Prepare to insert the new fee record into the database
    // Note the 'd' in bind_param: 'd' stands for Double (a decimal number, used for money/amount_paid)
    $stmt = $conn->prepare("INSERT INTO fees (student_id, amount_paid, payment_date, receipt_no, status, remarks) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idssss", $student_id, $amount_paid, $payment_date, $receipt_no, $status, $remarks);
    
    if ($stmt->execute()) {
        $success = "Payment recorded successfully! Receipt No: " . $receipt_no;
    } else {
        $error = "Error recording payment: " . $conn->error;
    }
}

// Fetch a list of all students to populate the dropdown menu in the form
$students = $conn->query("SELECT id, name, roll_no FROM students ORDER BY name ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Record Fee Payment</h4>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
</div>

<div class="card-table p-4 mt-0">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="create.php" method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Select Student</label>
                <select name="student_id" class="form-select" required>
                    <option value="">-- Select Student --</option>
                    <?php while ($row = $students->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>">
                            <?php echo htmlspecialchars($row['name']) . " (" . htmlspecialchars($row['roll_no']) . ")"; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Amount Paid ($)</label>
                <input type="number" step="0.01" name="amount_paid" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Payment Date</label>
                <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="Paid">Paid</option>
                    <option value="Pending">Pending</option>
                    <option value="Partial">Partial</option>
                </select>
            </div>
            <div class="col-md-12 mb-4">
                <label class="form-label">Remarks / Description</label>
                <input type="text" name="remarks" class="form-control" placeholder="e.g. Tuition Fee for March">
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-2"></i>Record Payment</button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
