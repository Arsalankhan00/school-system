<?php
// Connect to the database
require_once '../../includes/db.php';
// Load the header (sidebar, top navbar, and check if logged in)
require_once '../../includes/header.php';

// Handle the Search Bar
// $_GET is used to get data from the URL (like when you search for something, it adds ?search=Ali to the URL).
// '??' is a fallback. It means: "If there is no search word, just make $search an empty string."
$search = $_GET['search'] ?? '';
$where_clause = "";

// If the user typed something in the search bar
if (!empty($search)) {
    // real_escape_string cleans the input to stop hackers from doing SQL injection.
    $search_safe = $conn->real_escape_string($search);
    // We build a piece of SQL code that says "WHERE the name, roll_no, class_name, or section looks LIKE the search word"
    $where_clause = "WHERE name LIKE '%$search_safe%' OR roll_no LIKE '%$search_safe%' OR class_name LIKE '%$search_safe%' OR section LIKE '%$search_safe%'";
}

// Get the students from the database!
// If there was a search, $where_clause will filter the list. If not, it just grabs everyone.
// 'ORDER BY created_at DESC' puts the newest students at the top.
$students = $conn->query("SELECT * FROM students $where_clause ORDER BY created_at DESC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Students Management</h4>
    <a href="create.php" class="btn btn-primary-custom"><i class="fas fa-plus me-2"></i>Add Student</a>
</div>

<div class="card-table p-4 mt-0">
    <div class="row mb-3">
        <div class="col-md-6">
            <!-- This is the search form. When submitted, it refreshes the page and adds ?search=... to the URL -->
            <form action="index.php" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search by name, roll no, or class..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Roll No</th>
                    <th>Name</th>
                    <th>Father Name</th>
                    <th>Class</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Check if our database query worked and if it found more than 0 students.
                if ($students && $students->num_rows > 0): 
                ?>
                    <?php 
                    // This 'while' loop takes one row of data from the database at a time, 
                    // stores it in the '$row' variable, and runs the HTML below it.
                    while ($row = $students->fetch_assoc()): 
                    ?>
                        <tr>
                            <!-- We print out each piece of data. htmlspecialchars() keeps it secure. -->
                            <td><?php echo htmlspecialchars($row['roll_no']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['father_name']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['class_name']); ?>
                                <?php if (!empty($row['section'])): ?>
                                    <span class="text-muted">(<?php echo htmlspecialchars($row['section']); ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['contact']); ?></td>
                            <td>
                                <?php
                                $status_class = 'bg-secondary';
                                if ($row['status'] == 'Active') $status_class = 'bg-success';
                                elseif ($row['status'] == 'Inactive') $status_class = 'bg-danger';
                                elseif ($row['status'] == 'Graduated') $status_class = 'bg-info';
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                            </td>
                            <td>
                                <!-- Action Buttons: View, Edit, Delete -->
                                <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-success" title="View Details"><i class="fas fa-eye"></i></a>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Student"><i class="fas fa-edit"></i></a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this student?');" title="Delete Student"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php 
                    // End of the while loop
                    endwhile; 
                    ?>
                <?php else: ?>
                    <!-- If 0 students were found, show this message instead of an empty table -->
                    <tr><td colspan="7" class="text-center text-muted">No students found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
// Load the footer to close the HTML tags and add Javascript.
require_once '../../includes/footer.php'; 
?>
