<?php
session_start();

include('../includes/database.php');
// Assume the admin is logged in and we have their ID
$admin_id = $_SESSION['user_id'] ?? 1; // Replace with actual session handling

// Function to get total number of students
function getTotalStudents($conn) {
    $sql = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['total'];
}

// Function to get total number of lecturers
function getTotalLecturers($conn) {
    $sql = "SELECT COUNT(*) as total FROM users WHERE role = 'lecturer'";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['total'];
}

// Function to get upcoming events
function getUpcomingEvents($conn) {
    $sql = "SELECT * FROM events WHERE start_date >= CURDATE() ORDER BY start_date LIMIT 5";
    $result = $conn->query($sql);
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    return $events;
}

// Function to get overall student performance data
function getStudentPerformanceData($conn) {
    $sql = "SELECT MONTH(created_at) as month, AVG(CAST(grade AS DECIMAL(10,2))) as avg_grade 
            FROM grades 
            WHERE YEAR(created_at) = YEAR(CURDATE()) 
            GROUP BY MONTH(created_at) 
            ORDER BY MONTH(created_at)";
    $result = $conn->query($sql);
    $performanceData = [];
    while ($row = $result->fetch_assoc()) {
        $performanceData[] = $row;
    }
    return $performanceData;
}

// Function to get attendance data
function getAttendanceData($conn) {
    $sql = "SELECT status, COUNT(*) as count 
            FROM attendance 
            WHERE YEAR(attendance_date) = YEAR(CURDATE()) 
            GROUP BY status";
    $result = $conn->query($sql);
    $attendanceData = [];
    while ($row = $result->fetch_assoc()) {
        $attendanceData[] = $row;
    }
    return $attendanceData;
}

// Function to get scheduled classes
function getScheduledClasses($conn) {
    $sql = "SELECT * FROM classes WHERE class_date >= CURDATE() ORDER BY class_date LIMIT 5";
    $result = $conn->query($sql);
    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    return $classes;
}

// Function to get available courses
function getAvailableCourses($conn) {
    $sql = "SELECT * FROM courses";
    $result = $conn->query($sql);
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    return $courses;
}

$totalStudents = getTotalStudents($conn);
$totalLecturers = getTotalLecturers($conn);
$upcomingEvents = getUpcomingEvents($conn);
$performanceData = getStudentPerformanceData($conn);
$attendanceData = getAttendanceData($conn);
$scheduledClasses = getScheduledClasses($conn);
$availableCourses = getAvailableCourses($conn);

if (empty($upcomingEvents)) {
    echo "<script>console.warn('No upcoming events found');</script>";
}
if (empty($performanceData)) {
    echo "<script>console.warn('No performance data found');</script>";
}
if (empty($attendanceData)) {
    echo "<script>console.warn('No attendance data found');</script>";
}
if (empty($scheduledClasses)) {
    echo "<script>console.warn('No scheduled classes found');</script>";
}
if (empty($availableCourses)) {
    echo "<script>console.warn('No available courses found');</script>";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function checkLibraries() {
            if (typeof FullCalendar === 'undefined') {
                console.error('FullCalendar library is not loaded');
                alert('FullCalendar library failed to load. The calendar may not display correctly.');
            }
            if (typeof Chart === 'undefined') {
                console.error('Chart.js library is not loaded');
                alert('Chart.js library failed to load. Charts may not display correctly.');
            }
        }
        window.onload = checkLibraries;
    </script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-4xl font-bold mb-8 text-gray-800 flex items-center">
            <i class="fas fa-user-cog mr-4 text-blue-600"></i>
            Admin Dashboard
        </h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Total Students Card -->
            <div class="bg-white p-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <div class="flex items-center mb-4">
                    <i class="fas fa-user-graduate text-3xl text-blue-600 mr-4"></i>
                    <h2 class="text-xl font-semibold">Total Students</h2>
                </div>
                <p class="text-3xl font-bold text-blue-600"><?php echo $totalStudents; ?></p>
            </div>
            
            <!-- Total Lecturers Card -->
            <div class="bg-white p-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <div class="flex items-center mb-4">
                    <i class="fas fa-chalkboard-teacher text-3xl text-green-600 mr-4"></i>
                    <h2 class="text-xl font-semibold">Total Lecturers</h2>
                </div>
                <p class="text-3xl font-bold text-green-600"><?php echo $totalLecturers; ?></p>
            </div>
            
            <!-- Upcoming Events Card -->
            <div class="bg-white p-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <div class="flex items-center mb-4">
                    <i class="fas fa-calendar-alt text-3xl text-purple-600 mr-4"></i>
                    <h2 class="text-xl font-semibold">Upcoming Events</h2>
                </div>
                <ul class="list-none pl-0">
                    <?php foreach ($upcomingEvents as $event): ?>
                        <li class="mb-2 flex items-center">
                            <i class="fas fa-circle text-xs text-purple-600 mr-2"></i>
                            <?php echo htmlspecialchars($event['title']) . ' - ' . $event['start_date']; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
            <!-- Scheduled Classes Card -->
            <div class="bg-white p-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <div class="flex items-center mb-4">
                    <i class="fas fa-clock text-3xl text-yellow-600 mr-4"></i>
                    <h2 class="text-xl font-semibold">Scheduled Classes</h2>
                </div>
                <ul class="list-none pl-0">
                    <?php foreach ($scheduledClasses as $class): ?>
                        <li class="mb-2 flex items-center">
                            <i class="fas fa-circle text-xs text-yellow-600 mr-2"></i>
                            <?php echo htmlspecialchars($class['course_id']) . ' - ' . $class['class_date']; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Available Courses Card -->
            <div class="bg-white p-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <div class="flex items-center mb-4">
                    <i class="fas fa-book text-3xl text-teal-600 mr-4"></i>
                    <h2 class="text-xl font-semibold">Available Courses</h2>
                </div>
                <ul class="list-none pl-0">
                    <?php foreach ($availableCourses as $course): ?>
                        <li class="mb-2 flex items-center">
                            <i class="fas fa-circle text-xs text-teal-600 mr-2"></i>
                            <?php echo htmlspecialchars($course['course_name']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

<script>
    // Initialize Calendar
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: [ // Fetch events from the server or database
                // {
                //     title: 'Event Title',
                //     start: '2023-09-01'
                // },
            ]
        });
        calendar.render();
    });
</script>
</body>
</html>
