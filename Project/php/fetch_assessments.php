<?php
require '../includes/database.php';

if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];
    $query = "SELECT id, title FROM assessments WHERE course_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $assessments = [];
    while ($row = $result->fetch_assoc()) {
        $assessments[] = $row;
    }
    
    echo json_encode($assessments);
}
?>