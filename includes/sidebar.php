<?php
// Get the exact name of the current page you are viewing (for example: 'index.php')
$current_page = basename($_SERVER['PHP_SELF']);

// Get the name of the folder where the current page is saved (for example: 'students' or 'SMS')
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<nav id="sidebar">
    <div class="sidebar-header" style="background: var(--sidebar-bg) !important; background-image: none !important;">
        <i class="fas fa-graduation-cap me-2"></i> SMS Admin
    </div>
    <ul class="sidebar-menu">
        <li>
            <!-- Check if the current page is 'index.php' and the folder is 'SMS'. If yes, add the 'active' class to highlight this link -->
            <a href="/SMS/index.php" class="<?php echo ($current_page == 'index.php' && $current_dir == 'SMS') ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li>
            <!-- Check if the current folder is 'students'. If yes, add the 'active' class to highlight this link -->
            <a href="/SMS/modules/students/index.php" class="<?php echo ($current_dir == 'students') ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i> Students
            </a>
        </li>
        <li>
            <!-- Check if the current folder is 'classes'. If yes, add the 'active' class to highlight this link -->
            <a href="/SMS/modules/classes/index.php" class="<?php echo ($current_dir == 'classes') ? 'active' : ''; ?>">
                <i class="fas fa-school"></i> Classes
            </a>
        </li>
        <li>
            <!-- Check if the current folder is 'teachers'. If yes, add the 'active' class to highlight this link -->
            <a href="/SMS/modules/teachers/index.php" class="<?php echo ($current_dir == 'teachers') ? 'active' : ''; ?>">
                <i class="fas fa-chalkboard-teacher"></i> Teachers
            </a>
        </li>
        <li>
            <!-- Check if the current folder is 'courses'. If yes, add the 'active' class to highlight this link -->
            <a href="/SMS/modules/courses/index.php" class="<?php echo ($current_dir == 'courses') ? 'active' : ''; ?>">
                <i class="fas fa-book"></i> Courses
            </a>
        </li>
        <li>
            <!-- Check if the current folder is 'allocations'. If yes, add the 'active' class to highlight this link -->
            <a href="/SMS/modules/allocations/index.php" class="<?php echo ($current_dir == 'allocations') ? 'active' : ''; ?>">
                <i class="fas fa-chalkboard"></i> Allocations
            </a>
        </li>
        <li>
            <!-- Check if the current folder is 'fees'. If yes, add the 'active' class to highlight this link -->
            <a href="/SMS/modules/fees/index.php" class="<?php echo ($current_dir == 'fees') ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i> Fees
            </a>
        </li>
        <li>
            <!-- Check if the current folder is 'timetable'. If yes, add the 'active' class to highlight this link -->
            <a href="/SMS/modules/timetable/index.php" class="<?php echo ($current_dir == 'timetable') ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> Timetable
            </a>
        </li>
    </ul>
</nav>
