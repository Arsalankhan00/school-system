<?php
// We 'require' the db.php file so we can talk to the database using the $conn variable.
require_once 'includes/db.php';

// We 'require' the header.php file to check if the user is logged in and to load the HTML layout (like the top navigation).
require_once 'includes/header.php';

// Fetch Statistics for the Dashboard Cards
// Here, we ask the database to COUNT(*) how many rows are in the 'students' table.
// 'fetch_assoc()' turns the database answer into a PHP array, and we grab the ['count'] part.
// The '?? 0' part means: if there is an error or no data, just make the count 0 instead of crashing.
$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'] ?? 0;

// Count total rows in the 'teachers' table
$total_teachers = $conn->query("SELECT COUNT(*) as count FROM teachers")->fetch_assoc()['count'] ?? 0;

// Count total rows in the 'courses' table
$total_courses = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'] ?? 0;

// Count total rows in the 'classes' table
$total_classes = $conn->query("SELECT COUNT(*) as count FROM classes")->fetch_assoc()['count'] ?? 0;

// Calculate the total money collected.
// Instead of counting rows, we use SUM(amount_paid) to add up all the numbers in the 'amount_paid' column,
// but ONLY WHERE the status is 'Paid' (ignoring Pending fees).
$total_fees = $conn->query("SELECT SUM(amount_paid) as total FROM fees WHERE status='Paid'")->fetch_assoc()['total'] ?? 0;
?>

<div class="row g-4 row-cols-1 row-cols-md-3 row-cols-lg-5">
    <div class="col">
        <div class="stat-card">
            <div class="stat-info">
                <h3><?php echo number_format($total_students); ?></h3>
                <p>Total Students</p>
            </div>
            <div class="icon-box bg-gradient-primary">
                <i class="fas fa-user-graduate"></i>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card">
            <div class="stat-info">
                <h3><?php echo number_format($total_teachers); ?></h3>
                <p>Total Teachers</p>
            </div>
            <div class="icon-box bg-gradient-success">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card">
            <div class="stat-info">
                <h3><?php echo number_format($total_classes); ?></h3>
                <p>Total Classes</p>
            </div>
            <div class="icon-box" style="background: linear-gradient(135deg, #a855f7, #c084fc); box-shadow: 0 4px 10px rgba(168, 85, 247, 0.3);">
                <i class="fas fa-school"></i>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card">
            <div class="stat-info">
                <h3><?php echo number_format($total_courses); ?></h3>
                <p>Total Courses</p>
            </div>
            <div class="icon-box bg-gradient-warning">
                <i class="fas fa-book"></i>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card">
            <div class="stat-info" style="min-width: 0; padding-right: 10px;">
                <h3 style="font-size: 1.4rem; white-space: nowrap;">RS <?php echo number_format($total_fees, 0); ?></h3>
                <p>Fees Collected</p>
            </div>
            <div class="icon-box" style="background: linear-gradient(135deg, #0ea5e9, #38bdf8); box-shadow: 0 4px 10px rgba(14, 165, 233, 0.3);">
                <i class="fas fa-wallet"></i>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card-table p-4">
            <h5 class="fw-bold mb-4">Recent Students Enrolled</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Contact</th>
                            <th>Enrolled Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch the 5 most recently added students from the database.
                        // 'ORDER BY created_at DESC' means sort by date from newest to oldest.
                        // 'LIMIT 5' means only grab the first 5 records.
                        $recent_students = $conn->query("SELECT * FROM students ORDER BY created_at DESC LIMIT 5");
                        
                        // Check if the query worked AND if there is at least 1 student found (num_rows > 0).
                        if ($recent_students && $recent_students->num_rows > 0) {
                            
                            // A 'while' loop runs the code inside it over and over again for EVERY single row found in the database.
                            // 'fetch_assoc()' grabs the current row as an array (like a list) and stores it in the '$row' variable.
                            while ($row = $recent_students->fetch_assoc()) {
                                
                                // We 'echo' (print) HTML table rows <tr> and table data cells <td>.
                                // We inject variables like $row['name'] into the HTML to display the specific student's info.
                                // 'strtotime' and 'date' are used to format the database timestamp into a readable date like 'Apr 21, 2026'.
                                echo "<tr>
                                    <td>{$row['roll_no']}</td>
                                    <td>{$row['name']}</td>
                                    <td>{$row['class_name']}</td>
                                    <td>{$row['contact']}</td>
                                    <td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>
                                </tr>";
                            }
                        } else {
                            // If there are exactly 0 rows found, we print a friendly message telling the user the table is empty.
                            echo "<tr><td colspan='5' class='text-center text-muted'>No students found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3">
                <a href="/SMS/modules/students/index.php" class="btn btn-sm btn-outline-primary">View All Students</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
