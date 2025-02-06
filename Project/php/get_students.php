<?php
session_start();
require_once '../includes/database.php';

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lecturer') {
    header("Location: login-signup.php");
    exit();
}

$lecturer_id = $_SESSION['user_id'];

// Fetch students based on the selected course
if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];

    $students_query = "SELECT u.id, u.username, u.email, e.enrollment_date 
                       FROM users u 
                       JOIN enrollments e ON u.id = e.student_id 
                       WHERE e.course_id = ? AND u.role = 'student'";
    
    $students_stmt = $conn->prepare($students_query);
    $students_stmt->bind_param("i", $course_id);
    $students_stmt->execute();
    $students_result = $students_stmt->get_result();

    $students = $students_result->fetch_all(MYSQLI_ASSOC);

    // Output the students as JSON
    echo json_encode($students);
    exit();
}

$conn->close();
?>