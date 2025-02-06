<?php
session_start();

include('../includes/database.php');
// Assume the student is logged in and we have their ID
$student_id = $_SESSION['user_id'] ?? 1; // Replace with actual session handling

// Function to get student's courses with progress
function getStudentCourses($conn, $student_id) {
    $sql = "SELECT c.id, c.course_code, c.course_name, c.description, e.progress
            FROM courses c
            JOIN enrollments e ON c.id = e.course_id
            WHERE e.student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    return $courses;
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

// Function to get upcoming assignments
function getUpcomingAssignments($conn, $student_id) {
    $sql = "SELECT a.* FROM assessments a 
            JOIN enrollments e ON a.course_id = e.course_id 
            WHERE e.student_id = ? AND a.due_date >= CURDATE() 
            ORDER BY a.due_date LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignments = [];
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    return $assignments;
}

$studentCourses = getStudentCourses($conn, $student_id);
$upcomingEvents = getUpcomingEvents($conn);
$upcomingAssignments = getUpcomingAssignments($conn, $student_id);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-5xl font-bold mb-8 text-gray-800 flex items-center">
            <i class="fas fa-graduation-cap mr-4 text-blue-600"></i>
            Student Dashboard
        </h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Courses with Progress Bars -->
            <?php foreach ($studentCourses as $course): ?>
            <div class="bg-white p-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <div class="flex items-center mb-4">
                    <i class="fas fa-book text-2xl text-blue-600 mr-3"></i>
                    <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($course['course_name']); ?></h2>
                </div>
                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($course['course_code']); ?></p>
                <p class="text-sm mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                <div class="relative pt-1">
                    <div class="flex mb-2 items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-blue-600 bg-blue-200">
                                Progress
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-semibold inline-block text-blue-600">
                                <?php echo $course['progress']; ?>%
                            </span>
                        </div>
                    </div>
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
                        <div style="width:<?php echo $course['progress']; ?>%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-600 transition-all duration-300 ease-in-out"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Upcoming Events Card -->
            <div class="bg-white p-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <div class="flex items-center mb-4">
                    <i class="fas fa-calendar-check text-2xl text-purple-600 mr-3"></i>
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
            
            <!-- Upcoming Assignments Card -->
            <div class="bg-white p-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <div class="flex items-center mb-4">
                    <i class="fas fa-tasks text-2xl text-red-600 mr-3"></i>
                    <h2 class="text-xl font-semibold">Upcoming Assignments</h2>
                </div>
                <ul class="list-none pl-0">
                    <?php foreach ($upcomingAssignments as $assignment): ?>
                        <li class="mb-2 flex items-center">
                            <i class="fas fa-clipboard-list text-red-600 mr-2"></i>
                            <?php echo htmlspecialchars($assignment['title']) . ' - Due: ' . $assignment['due_date']; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: [
                    <?php foreach ($upcomingEvents as $event): ?>
                    ,{
                        title: '<?php echo addslashes($event['title']); ?>',
                        start: '<?php echo $event['start_date']; ?>',
                        end: '<?php echo $event['end_date'] ?? $event['start_date']; ?>',
                        color: '#8B5CF6'
                    },
                    <?php endforeach; ?>
                    <?php foreach ($upcomingAssignments as $assignment): ?>
                    ,{
                        title: 'Due: <?php echo addslashes($assignment['title']); ?>',
                        start: '<?php echo $assignment['due_date']; ?>',
                        color: '#EF4444'
                    },
                    <?php endforeach; ?>
                ],
                eventClick: function(info) {
                    alert('Event: ' + info.event.title);
                }
            });
            calendar.render();
        });
    </script>
</body>
</html>