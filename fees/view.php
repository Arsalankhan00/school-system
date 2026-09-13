<?php
// Connect to the database and verify the admin is logged in
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// Get the fee record's ID from the URL. If not provided, default to 0.
$id = $_GET['id'] ?? 0;

// Fetch fee record details using JOINs to get student details
$query = "SELECT f.*, s.name as student_name, s.roll_no, s.class_name, s.email as student_email 
          FROM fees f 
          JOIN students s ON f.student_id = s.id 
          WHERE f.id = ?";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$fee = $stmt->get_result()->fetch_assoc();

// If the fee record doesn't exist, show an error and exit
if (!$fee) {
    echo "<div class='alert alert-danger'>Fee record not found.</div>";
    require_once '../../includes/footer.php';
    exit();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Fee Payment Details</h4>
    <div>
        <a href="index.php" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
        <a href="receipt.php?id=<?php echo $fee['id']; ?>" class="btn btn-outline-info me-2" target="_blank"><i class="fas fa-print me-2"></i>Print Receipt</a>
        <a href="edit.php?id=<?php echo $fee['id']; ?>" class="btn btn-primary-custom"><i class="fas fa-edit me-2"></i>Edit Payment</a>
    </div>
</div>

<div class="row">
    <!-- Fee Detail Card -->
    <div class="col-md-6 mb-4 mx-auto">
        <div class="card-table p-4 mt-0">
            <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                <div class="d-flex align-items-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-gradient-success text-white rounded-circle me-3" style="width: 60px; height: 60px; font-size: 1.6rem; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Receipt: <?php echo htmlspecialchars($fee['receipt_no']); ?></h5>
                        <p class="text-muted mb-0">Date: <?php echo date('F d, Y', strtotime($fee['payment_date'])); ?></p>
                    </div>
                </div>
                <div>
                    <?php if ($fee['status'] == 'Paid'): ?>
                        <span class="badge bg-success fs-6 px-3 py-2">Paid</span>
                    <?php elseif ($fee['status'] == 'Pending'): ?>
                        <span class="badge bg-danger fs-6 px-3 py-2">Pending</span>
                    <?php else: ?>
                        <span class="badge bg-warning fs-6 px-3 py-2">Partial</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <td class="text-muted fw-bold pb-2" style="width: 40%;">Student Name:</td>
                            <td class="pb-2">
                                <a href="../students/view.php?id=<?php echo $fee['student_id']; ?>" class="text-decoration-none fw-bold text-dark">
                                    <?php echo htmlspecialchars($fee['student_name']); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold pb-2">Roll Number:</td>
                            <td class="pb-2"><strong><?php echo htmlspecialchars($fee['roll_no']); ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold pb-2">Class:</td>
                            <td class="pb-2"><?php echo htmlspecialchars($fee['class_name']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold pb-2">Student Email:</td>
                            <td class="pb-2"><?php echo htmlspecialchars($fee['student_email']); ?></td>
                        </tr>
                        <tr class="border-top">
                            <td class="text-muted fw-bold pt-3 pb-2">Amount Paid:</td>
                            <td class="pt-3 pb-2"><h4 class="fw-bold text-primary mb-0">RS <?php echo number_format($fee['amount_paid'], 0); ?></h4></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold pb-2">Remarks/Notes:</td>
                            <td class="pb-2" style="white-space: pre-line;"><?php echo htmlspecialchars($fee['remarks'] ? $fee['remarks'] : 'No remarks.'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
