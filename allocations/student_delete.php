<?php
require_once '../../includes/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Fetch enrollment details before deleting for history logging
    $fetch_stmt = $conn->prepare("
        SELECT sc.student_id, c.course_name, c.course_code 
        FROM student_courses sc 
        JOIN courses c ON sc.course_id = c.id 
        WHERE sc.id = ?
    ");
    $fetch_stmt->bind_param("i", $id);
    $fetch_stmt->execute();
    $enrollment = $fetch_stmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare("DELETE FROM student_courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($enrollment) {
            $student_id = $enrollment['student_id'];
            $desc = "Removed from course: " . $enrollment['course_name'] . " (" . $enrollment['course_code'] . ")";
            log_student_event($student_id, 'Course Removal', $desc);
        }
        header("Location: index.php?success=Assignment removed successfully");
    } else {
        header("Location: index.php?error=Error removing assignment");
    }
} else {
    header("Location: index.php");
}
?>
