<?php
session_start();
require_once '../includes/database.php';

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lecturer') {
    header("Location: login-signup.php");
    exit();
}

$lecturer_id = $_SESSION['user_id'];

// Fetch courses taught by the lecturer
$courses_query = "SELECT id, course_name FROM courses WHERE lecturer_id = ?";
$courses_stmt = $conn->prepare($courses_query);
$courses_stmt->bind_param("i", $lecturer_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();
$courses = $courses_result->fetch_all(MYSQLI_ASSOC);

// Fetch students for the first course (default view)
$students = [];
if (!empty($courses)) {
    $default_course_id = $courses[0]['id'];
    $students_query = "SELECT u.id, u.username, u.email, e.enrollment_date 
                       FROM users u 
                       JOIN enrollments e ON u.id = e.student_id 
                       WHERE e.course_id = ? AND u.role = 'student'";
    $students_stmt = $conn->prepare($students_query);
    $students_stmt->bind_param("i", $default_course_id);
    $students_stmt->execute();
    $students_result = $students_stmt->get_result();
    $students = $students_result->fetch_all(MYSQLI_ASSOC);
}

$conn->close();

?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Tracking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body>
    <div id="my_students" class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">My Students</h1>
        
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <label for="course-select" class="block text-sm font-medium text-gray-700 mb-2">Select Course:</label>
                <select id="course-select" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" onchange="loadStudents(this.value)">
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrollment Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="students-list" class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($student['username']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($student['enrollment_date']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button class="text-indigo-600 hover:text-indigo-900" onclick="viewStudentDetails(<?php echo $student['id']; ?>)">View Details</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center px-6 py-4">No students enrolled in this course.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="student-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center">
        <div class="bg-white rounded-lg p-6 max-w-md mx-auto">
            <h2 class="text-2xl font-semibold mb-4">Student Details</h2>
            <div id="modal-content" class="space-y-4">
                <!-- Content will be filled dynamically -->
            </div>
            <button onclick="closeModal()" class="mt-4 bg-red-500 text-white px-4 py-2 rounded">Close</button>
        </div>
    </div>

    <script>
        function loadStudents(courseId) {
            window.location.href = `?course_id=${courseId}`;
        }

        function viewStudentDetails(studentId) {
            // Here you can make an AJAX call or directly fill the modal content for demonstration
            const studentData = {
                1: { username: 'john_doe', email: 'john@example.com', courses: ['Math', 'Science'], profile_photo: 'path/to/photo1.jpg' },
                2: { username: 'jane_smith', email: 'jane@example.com', courses: ['English', 'History'], profile_photo: 'path/to/photo2.jpg' },
                // Add more student data as needed
            };

            const student = studentData[studentId];
            if (student) {
                document.getElementById('modal-content').innerHTML = `
                    <div class="flex items-center">
                        <img src="${student.profile_photo}" alt="${student.username}" class="h-16 w-16 rounded-full mr-4">
                        <div>
                            <h3 class="text-lg font-bold">${student.username}</h3>
                            <p class="text-sm text-gray-600">${student.email}</p>
                            <p class="text-sm text-gray-600">Courses: ${student.courses.join(', ')}</p>
                        </div>
                    </div>
                `;
                document.getElementById('student-modal').classList.remove('hidden');
            }
        }

        function closeModal() {
            document.getElementById('student-modal').classList.add('hidden');
        }
    </script>
</body>