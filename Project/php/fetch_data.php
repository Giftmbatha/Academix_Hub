<?php
header('Content-Type: application/json');
include_once('../includes/database.php');
// Fetch student performance
$performanceQuery = "SELECT course_name, AVG(grade) as average_grade FROM grades 
                      JOIN classes ON grades.class_id = classes.id 
                      JOIN courses ON classes.course_id = courses.id 
                      GROUP BY course_name";
$performanceResult = $mysqli->query($performanceQuery);

$performanceData = ['labels' => [], 'grades' => []];
while ($row = $performanceResult->fetch_assoc()) {
    $performanceData['labels'][] = $row['course_name'];
    $performanceData['grades'][] = $row['average_grade'];
}

// Fetch attendance
$attendanceQuery = "SELECT class_date, COUNT(*) as attendance_count FROM attendance 
                    WHERE status = 'Present' GROUP BY class_date";
$attendanceResult = $mysqli->query($attendanceQuery);

$attendanceData = ['labels' => [], 'attendance' => []];
while ($row = $attendanceResult->fetch_assoc()) {
    $attendanceData['labels'][] = $row['class_date'];
    $attendanceData['attendance'][] = $row['attendance_count'];
}

// Fetch announcements
$announcementsQuery = "SELECT title, start_date as date FROM events ORDER BY start_date DESC LIMIT 5";
$announcementsResult = $mysqli->query($announcementsQuery);

$announcementsData = [];
while ($row = $announcementsResult->fetch_assoc()) {
    $announcementsData[] = $row;
}

echo json_encode([
    'performance' => $performanceData,
    'attendance' => $attendanceData,
    'announcements' => $announcementsData,
]);

$mysqli->close();
?>