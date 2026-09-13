<?php
require_once '../../includes/db.php';
require_once '../../includes/header.php';

// --- Total Collections ---
$total_collected = $conn->query("SELECT SUM(amount_paid) as total FROM fees")->fetch_assoc()['total'] ?? 0;

// --- Collections by Month (Current Year) ---
$monthly_query = "SELECT MONTHNAME(payment_date) as month, SUM(amount_paid) as amount 
                 FROM fees 
                 WHERE YEAR(payment_date) = YEAR(CURDATE()) 
                 GROUP BY MONTH(payment_date) 
                 ORDER BY MONTH(payment_date) ASC";
$monthly_data = $conn->query($monthly_query);

// --- Collections by Status ---
$status_query = "SELECT status, COUNT(*) as count, SUM(amount_paid) as total FROM fees GROUP BY status";
$status_data = $conn->query($status_query);

// --- Pending Estimates ---
// This is a rough estimate based on (Total Students in a class * Fee for that class) - (Actually Paid for that month)
// For simplicity, we'll just show "Expected vs Collected" for the current month
$current_month = date('m');
$current_year = date('Y');

$expected_query = "SELECT SUM(fs.amount) as expected 
                  FROM students s 
                  JOIN fee_structure fs ON s.class_name = fs.class_name 
                  WHERE s.status = 'Active'";
$total_expected = $conn->query($expected_query)->fetch_assoc()['expected'] ?? 0;

$this_month_collected = $conn->query("SELECT SUM(amount_paid) as total FROM fees WHERE MONTH(payment_date) = $current_month AND YEAR(payment_date) = $current_year")->fetch_assoc()['total'] ?? 0;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Financial Reporting</h4>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Fees</a>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card p-4 text-white bg-primary shadow-sm border-0">
            <h6 class="text-uppercase mb-2 opacity-75">Total Collections (All Time)</h6>
            <h2 class="fw-bold mb-0">RS <?php echo number_format($total_collected, 0); ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 text-white bg-success shadow-sm border-0">
            <h6 class="text-uppercase mb-2 opacity-75">This Month Collected</h6>
            <h2 class="fw-bold mb-0">RS <?php echo number_format($this_month_collected, 0); ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 text-white bg-warning shadow-sm border-0">
            <h6 class="text-uppercase mb-2 opacity-75">Estimated Pending (Monthly)</h6>
            <h2 class="fw-bold mb-0">RS <?php echo number_format(max(0, $total_expected - $this_month_collected), 0); ?></h2>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card-table p-4">
            <h5 class="mb-4">Monthly Collection Trend (<?php echo date('Y'); ?>)</h5>
            <canvas id="monthlyChart" height="250"></canvas>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card-table p-4">
            <h5 class="mb-4">Collections by Status</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $status_data->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="badge <?php echo ($row['status'] == 'Paid') ? 'bg-success' : (($row['status'] == 'Pending') ? 'bg-danger' : 'bg-warning'); ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $row['count']; ?></td>
                                <td>RS <?php echo number_format($row['total'], 0); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    
    <?php
    $labels = [];
    $values = [];
    if ($monthly_data) {
        while($row = $monthly_data->fetch_assoc()) {
            $labels[] = $row['month'];
            $values[] = $row['amount'];
        }
    }
    ?>

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Collections (RS)',
                data: <?php echo json_encode($values); ?>,
                borderColor: '#4F46E5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#4F46E5',
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
