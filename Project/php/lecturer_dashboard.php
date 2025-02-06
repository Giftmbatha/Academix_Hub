<?php
session_start();

include('../includes/database.php');
// Assume the lecturer is logged in and we have their ID
$lecturer_id = $_SESSION['user_id'] ?? 1; // Replace with actual session handling


// Function to get total students for lecturer's courses
function getTotalStudents($conn, $lecturer_id) {
    $sql = "SELECT COUNT(DISTINCT e.student_id) as total_students 
            FROM enrollments e 
            JOIN courses c ON e.course_id = c.id 
            WHERE c.lecturer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lecturer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total_students'];
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

// Function to get lecturer's courses
function getLecturerCourses($conn, $lecturer_id) {
    $sql = "SELECT * FROM courses WHERE lecturer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lecturer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    return $courses;
}

// Function to get upcoming submissions
function getUpcomingSubmissions($conn, $lecturer_id) {
    $sql = "SELECT a.* FROM assessments a 
            JOIN courses c ON a.course_id = c.id 
            WHERE c.lecturer_id = ? AND a.due_date >= CURDATE() 
            ORDER BY a.due_date LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lecturer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $submissions = [];
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
    return $submissions;
}

$totalStudents = getTotalStudents($conn, $lecturer_id);
$upcomingEvents = getUpcomingEvents($conn);
$lecturerCourses = getLecturerCourses($conn, $lecturer_id);
$upcomingSubmissions = getUpcomingSubmissions($conn, $lecturer_id);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.2/main.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.2/main.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-5xl font-bold mb-8 text-gray-800">Lecturer Dashboard</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Total Students Card -->
            <div class="bg-white p-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <div class="flex items-center mb-4">
                    <i class="fas fa-users text-3xl text-blue-500 mr-4"></i>
                    <h2 class="text-xl font-semibold text-gray-700">My Students</h2>
                </div>
                <p class="text-3xl font-bold text-blue-600"><?php echo $totalStudents; ?></p>
            </div>
            
            <!-- Upcoming Events Card -->
            <div class="bg-white p-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <div class="flex items-center mb-4">
                    <i class="fas fa-calendar-alt text-3xl text-green-500 mr-4"></i>
                    <h2 class="text-xl font-semibold text-gray-700">Upcoming Events</h2>
                </div>
                <ul class="list-none pl-0">
                    <?php foreach ($upcomingEvents as $event): ?>
                        <li class="mb-2 flex items-center">
                            <i class="fas fa-circle text-xs text-green-500 mr-2"></i>
                            <?php echo htmlspecialchars($event['title']) . ' - ' . $event['start_date']; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Calendar Card -->
            <div class="bg-white p-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <div class="flex items-center mb-4">
                    <i class="fas fa-calendar-check text-3xl text-purple-500 mr-4"></i>
                    <h2 class="text-xl font-semibold text-gray-700">Calendar</h2>
                </div>
                <div id="calendar"></div>
            </div>
            
            <!-- Lecturer's Courses Card -->
            <div class="bg-white p-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <div class="flex items-center mb-4">
                    <i class="fas fa-book text-3xl text-yellow-500 mr-4"></i>
                    <h2 class="text-xl font-semibold text-gray-700">My Courses</h2>
                </div>
                <ul class="list-none pl-0">
                    <?php foreach ($lecturerCourses as $course): ?>
                        <li class="mb-2 flex items-center">
                            <i class="fas fa-graduation-cap text-yellow-500 mr-2"></i>
                            <?php echo htmlspecialchars($course['course_name']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Upcoming Submissions Card -->
            <div class="bg-white p-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <div class="flex items-center mb-4">
                    <i class="fas fa-clipboard-list text-3xl text-red-500 mr-4"></i>
                    <h2 class="text-xl font-semibold text-gray-700">Upcoming Submissions</h2>
                </div>
                <ul class="list-none pl-0">
                    <?php foreach ($upcomingSubmissions as $submission): ?>
                        <li class="mb-2 flex items-center">
                            <i class="fas fa-file-alt text-red-500 mr-2"></i>
                            <?php echo htmlspecialchars($submission['title']) . ' - Due: ' . $submission['due_date']; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Actions Card -->
            <div class="bg-white p-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <div class="flex items-center mb-4">
                    <i class="fas fa-tasks text-3xl text-indigo-500 mr-4"></i>
                    <h2 class="text-xl font-semibold text-gray-700">Actions</h2>
                </div>
                <div class="flex flex-col space-y-4">
                    <button id="uploadAssessmentBtn" class="bg-purple-600 hover:bg-purple-400 text-white font-bold py-2 px-4 rounded flex items-center justify-center transition duration-300 ease-in-out">
                        <i class="fas fa-upload mr-2"></i> Upload Assessment
                    </button>
                    <button id="addGradesBtn" class="bg-purple-600 hover:bg-purple-400 text-white font-bold py-2 px-4 rounded flex items-center justify-center transition duration-300 ease-in-out">
                        <i class="fas fa-plus-circle mr-2"></i> Add Grades
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Upload Assessment -->
    <div id="uploadAssessmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white p-8 rounded-lg max-w-md w-full">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Upload Assessment</h2>
            <form id="uploadAssessmentForm">
                <div class="mb-4">
                    <label for="assessmentTitle" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" id="assessmentTitle" name="title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                </div>
                <div class="mb-4">
                    <label for="assessmentDueDate" class="block text-sm font-medium text-gray-700">Due Date</label>
                    <input type="date" id="assessmentDueDate" name="dueDate" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                </div>
                <div class="mb-4">
                    <label for="assessmentDetails" class="block text-sm font-medium text-gray-700">Details</label>
                    <textarea id="assessmentDetails" name="details" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out">Upload</button>
                    <button type="button" onclick="closeModal('uploadAssessmentModal')" class="ml-2 bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded transition duration-300 ease-in-out">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Add Grades -->
    <div id="addGradesModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white p-8 rounded-lg max-w-md w-full">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Add Grades</h2>
            <form id="addGradesForm">
                <div class="mb-4">
                    <label for="gradesCourse" class="block text-sm font-medium text-gray-700">Course</label>
                    <select id="gradesCourse" name="course" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                        <?php foreach ($lecturerCourses as $course): ?>
                            <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="gradesAssessment" class="block text-sm font-medium text-gray-700">Assessment</label>
                    <select id="gradesAssessment" name="assessment" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                        <!-- Options will be populated dynamically based on selected course -->
                    </select>
                </div>
                <div id="gradesEntries">
                    <!-- Grade entries will be added here dynamically -->
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out">Save Grades</button>
                    <button type="button" onclick="closeModal('addGradesModal')" class="ml-2 bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded transition duration-300 ease-in-out">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [
                    <?php foreach ($upcomingEvents as $event): ?>
                    ,{
                        title: '<?php echo addslashes($event['title']); ?>',
                        start: '<?php echo $event['start_date']; ?>',
                        end: '<?php echo $event['end_date'] ?? $event['start_date']; ?>'
                    },
                    <?php endforeach; ?>
                ]
            });
            calendar.render();

            // Upload Assessment Modal
            document.getElementById('uploadAssessmentBtn').addEventListener('click', function() {
                document.getElementById('uploadAssessmentModal').classList.remove('hidden');
                document.getElementById('uploadAssessmentModal').classList.add('flex');
            });

            // Add Grades Modal
            document.getElementById('addGradesBtn').addEventListener('click', function() {
                document.getElementById('addGradesModal').classList.remove('hidden');
                document.getElementById('addGradesModal').classList.add('flex');
            });

            // Form submission handlers
            document.getElementById('uploadAssessmentForm').addEventListener('submit', function(e) {
                e.preventDefault();
                // Here you would typically send an AJAX request to submit the form data
                alert('Assessment uploaded successfully!');
                closeModal('uploadAssessmentModal');
            });

            document.getElementById('addGradesForm').addEventListener('submit', function(e) {
                e.preventDefault();
                // Here you would typically send an AJAX request to submit the form data
                alert('Grades added successfully!');
                closeModal('addGradesModal');
            });

            // Populate assessments based on selected course
            document.getElementById('gradesCourse').addEventListener('change', function() {
                // This would typically be an AJAX call to get assessments for the selected course
                // For now, we'll just populate with dummy data
                var assessmentSelect = document.getElementById('gradesAssessment');
                assessmentSelect.innerHTML = '';
                ['Midterm', 'Final Exam', 'Project'].forEach(function(assessment, index) {
                    var option = document.createElement('option');
                    option.value = index + 1;
                    option.textContent = assessment;
                    assessmentSelect.appendChild(option);
                });
            });

            // Populate grade entries when assessment is selected
            document.getElementById('gradesAssessment').addEventListener('change', function() {
                var gradesEntries = document.getElementById('gradesEntries');
                gradesEntries.innerHTML = '';
                // This would typically be an AJAX call to get students for the selected course and assessment
                // For now, we'll just populate with dummy data
                ['John Doe', 'Jane Smith', 'Bob Johnson'].forEach(function(student, index) {
                    var entry = document.createElement('div');
                    entry.className = 'mb-2';
                    entry.innerHTML = `
                        <label class="block text-sm font-medium text-gray-700">${student}</label>
                        <input type="number" name="grade[${index}]" min="0" max="100" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                    `;
                    gradesEntries.appendChild(entry);
                });
            });
        });

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            document.getElementById(modalId).classList.remove('flex');
        }
    </script>
</body>
</html