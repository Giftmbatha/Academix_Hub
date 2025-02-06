<?php
session_start();
require_once '../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login-signup.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'student') {
    // Fetch courses and progress for the student
    $stmt = $conn->prepare("
        SELECT c.id AS class_id, co.course_name, c.schedule_time, c.room, c.class_date 
        FROM enrollments e
        JOIN classes c ON e.course_id = c.course_id
        JOIN courses co ON c.course_id = co.id
        WHERE e.student_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $courses_result = $stmt->get_result();
    $courses = $courses_result->fetch_all(MYSQLI_ASSOC);

    // Fetch assessments for the student's courses, including file_path for documents
    $stmt = $conn->prepare("
        SELECT a.id AS assessment_id, a.title, a.due_date, a.file_path, co.course_name 
        FROM assessments a
        JOIN classes c ON a.class_id = c.id
        JOIN courses co ON c.course_id = co.id
        JOIN enrollments e ON e.course_id = c.course_id
        WHERE e.student_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $assessments_result = $stmt->get_result();
    $assessments = $assessments_result->fetch_all(MYSQLI_ASSOC);

} elseif ($role === 'lecturer') {
    // Fetch courses taught by the lecturer
    $stmt = $conn->prepare("
        SELECT c.id AS class_id, co.course_name, c.schedule_time, c.class_date,c.room 
        FROM classes c
        JOIN courses co ON c.course_id = co.id
        WHERE c.lecturer_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $courses_result = $stmt->get_result();
    $courses = $courses_result->fetch_all(MYSQLI_ASSOC);

    // Fetch assessments assigned by the lecturer
    $stmt = $conn->prepare("
        SELECT a.id AS assessment_id, a.title, a.due_date, co.course_name 
        FROM assessments a
        JOIN classes c ON a.class_id = c.id
        JOIN courses co ON c.course_id = co.id
        WHERE c.lecturer_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $assessments_result = $stmt->get_result();
    $assessments = $assessments_result->fetch_all(MYSQLI_ASSOC);
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
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8 text-black">My Courses</h1>

        <?php if ($role === 'student'): ?>
            <div class="grid md:grid-cols-2 gap-8 mb-12">
                <div>
                    <h2 class="text-2xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-book-open mr-2 text-purple-500"></i>Enrolled Courses
                    </h2>
                    <div class="space-y-6">
                        <?php if (!empty($courses)): ?>
                            <?php foreach ($courses as $course): ?>
                                <div class="bg-white rounded-lg shadow-md p-6 transition duration-300 ease-in-out hover:shadow-lg">
                                    <h3 class="font-bold text-xl mb-2"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                                    <p class="text-gray-600 mb-1"><i class="far fa-clock mr-2"></i><?php echo htmlspecialchars($course['schedule_time']); ?></p>
                                    <p class="text-gray-600 mb-1"><i class="fas fa-calendar mr-2"></i><?php echo htmlspecialchars($course['class_date']); ?></p>
                                    <p class="text-gray-600 mb-3"><i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($course['room']); ?></p>
                                    
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-gray-600 bg-white rounded-lg shadow-md p-6">You are not currently enrolled in any courses.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <h2 class="text-2xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-tasks mr-2 text-purple-500"></i>Available Assessments
                    </h2>
                    <div class="space-y-6">
                        <?php if (!empty($assessments)): ?>
                            <?php foreach ($assessments as $assessment): ?>
                                <div class="bg-white rounded-lg shadow-md p-6 transition duration-300 ease-in-out hover:shadow-lg">
                                    <h3 class="font-bold text-xl mb-2"><?php echo htmlspecialchars($assessment['title']); ?></h3>
                                    <p class="text-gray-600 mb-1"><i class="fas fa-graduation-cap mr-2"></i><?php echo htmlspecialchars($assessment['course_name']); ?></p>
                                    <p class="text-gray-600 mb-3"><i class="far fa-calendar-alt mr-2"></i>Due: <?php echo htmlspecialchars($assessment['due_date']); ?></p>
                                    <?php if (!empty($assessment['file_path'])): ?>
                                        <a href="<?php echo htmlspecialchars($assessment['file_path']); ?>" target="_blank" class="inline-block mt-2 px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 transition duration-300">
                                            <i class="fas fa-download mr-2"></i>Download Assessment
                                        </a>
                                    <?php else: ?>
                                        <p class="text-gray-500 italic mt-2">No file available for this assessment.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-gray-600 bg-white rounded-lg shadow-md p-6">There are no assessments available for your courses.</p>
                        <?php endif; ?>
                    </div>
                </div>
                    <div class="mt-12">
            <h2 class="text-2xl font-semibold mb-4 flex items-center">
                <i class="fas fa-user-plus mr-2 text-purple-500"></i>Register for a Course
            </h2>
            <a href="/php/course_registration.php" class="inline-block px-6 py-3 bg-purple-600 text-white font-bold rounded-lg hover:bg-purple-700 transition duration-300 shadow-md hover:shadow-lg">
                Register Now
            </a>
        </div>
            </div>

        <?php elseif ($role === 'lecturer'): ?>
            <div class="grid md:grid-cols-2 gap-8 mb-12">
                <div>
                    <h2 class="text-2xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-chalkboard-teacher mr-2 text-purple-500"></i>Courses Taught
                    </h2>
                    <div class="space-y-6">
                        <?php if (!empty($courses)): ?>
                            <?php foreach ($courses as $course): ?>
                                <div class="bg-white rounded-lg shadow-md p-6 transition duration-300 ease-in-out hover:shadow-lg">
                                    <h3 class="font-bold text-xl mb-2"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                                    <p class="text-gray-600 mb-1"><i class="far fa-clock mr-2"></i><?php echo htmlspecialchars($course['schedule_time']); ?></p>
                                    <p class="text-gray-600"><i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($course['room']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-gray-600 bg-white rounded-lg shadow-md p-6">You are not currently teaching any courses.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <h2 class="text-2xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-clipboard-list mr-2 text-purple-500"></i>Assigned Assessments
                    </h2>
                    <div class="space-y-6">
                        <?php if (!empty($assessments)): ?>
                            <?php foreach ($assessments as $assessment): ?>
                                <div class="bg-white rounded-lg shadow-md p-6 transition duration-300 ease-in-out hover:shadow-lg">
                                    <h3 class="font-bold text-xl mb-2"><?php echo htmlspecialchars($assessment['title']); ?></h3>
                                    <p class="text-gray-600 mb-1"><i class="fas fa-graduation-cap mr-2"></i><?php echo htmlspecialchars($assessment['course_name']); ?></p>
                                    <p class="text-gray-600"><i class="far fa-calendar-alt mr-2"></i>Due: <?php echo htmlspecialchars($assessment['due_date']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-gray-600 bg-white rounded-lg shadow-md p-6">You have not assigned any assessments yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
