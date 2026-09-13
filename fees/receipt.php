<?php
// Start session and verify the admin is logged in. 
// If not, redirect them to the login page.
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../../login.php");
    exit();
}
require_once '../../includes/db.php';

// Get the specific Receipt ID from the URL (e.g., receipt.php?id=12)
$id = $_GET['id'] ?? 0;

// Write a JOIN query to get the fee details AND the associated student's details
$stmt = $conn->prepare("SELECT fees.*, students.name, students.roll_no, students.class_name 
                        FROM fees 
                        JOIN students ON fees.student_id = students.id 
                        WHERE fees.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$receipt = $stmt->get_result()->fetch_assoc();

// If someone tries to access a receipt ID that doesn't exist, stop the script and show an error.
if (!$receipt) {
    die("Receipt not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fee Receipt - <?php echo $receipt['receipt_no']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; padding: 40px; }
        .receipt-card { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
        .receipt-header { border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 20px; text-align: center; }
        @media print {
            body { background-color: #fff; padding: 0; }
            .receipt-card { box-shadow: none; border: 1px solid #ddd; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="receipt-card">
        <div class="receipt-header">
            <h3>School Management System</h3>
            <p class="text-muted mb-0">Official Fee Receipt</p>
        </div>
        
        <div class="row mb-4">
            <div class="col-sm-6">
                <strong>Receipt No:</strong> <?php echo $receipt['receipt_no']; ?><br>
                <strong>Date:</strong> <?php echo date('M d, Y', strtotime($receipt['payment_date'])); ?>
            </div>
            <div class="col-sm-6 text-end">
                <strong>Status:</strong> <span class="badge bg-success"><?php echo $receipt['status']; ?></span>
            </div>
        </div>

        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th width="40%">Student Name</th>
                    <td><?php echo htmlspecialchars($receipt['name']); ?></td>
                </tr>
                <tr>
                    <th>Roll Number</th>
                    <td><?php echo htmlspecialchars($receipt['roll_no']); ?></td>
                </tr>
                <tr>
                    <th>Class</th>
                    <td><?php echo htmlspecialchars($receipt['class_name']); ?></td>
                </tr>
                <tr>
                    <th>Remarks/Description</th>
                    <td><?php echo htmlspecialchars($receipt['remarks']); ?></td>
                </tr>
                <tr class="table-active">
                    <th>Amount Paid</th>
                    <td><strong>RS <?php echo number_format($receipt['amount_paid'], 0); ?></strong></td>
                </tr>
            </tbody>
        </table>

        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print Receipt</button>
            <a href="index.php" class="btn btn-secondary">Back</a>
        </div>
    </div>
</body>
</html>
