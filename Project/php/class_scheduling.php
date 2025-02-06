<?php
session_start();
require_once '../includes/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-signup.php");
    exit();
}

// Fetch courses and lecturers
$courses = $conn->query("SELECT id, course_name, course_code FROM courses")->fetch_all(MYSQLI_ASSOC);
$lecturers = $conn->query("SELECT id, username FROM users WHERE role = 'lecturer'")->fetch_all(MYSQLI_ASSOC);

// Handle class scheduling
if (isset($_POST['class_date']) && isset($_POST['course_id']) && isset($_POST['lecturer_id'])) {
    $course_id = $_POST['course_id'];
    $lecturer_id = $_POST['lecturer_id'];
    $schedule_time = $_POST['schedule_time'];
    $class_date = $_POST['class_date'];
    $room = $_POST['room'];

    $stmt = $conn->prepare("INSERT INTO classes (course_id, lecturer_id, schedule_time, class_date, room) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $course_id, $lecturer_id, $schedule_time, $class_date, $room);
    
    if ($stmt->execute()) {
        $success_message = "Class scheduled successfully.";
    } else {
        $error_message = "Error scheduling class: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch scheduled classes
$scheduled_classes = $conn->query("
    SELECT c.id, co.course_name, u.username as lecturer_name, c.schedule_time, c.class_date, c.room 
    FROM classes c
    JOIN courses co ON c.course_id = co.id
    JOIN users u ON c.lecturer_id = u.id
    ORDER BY c.class_date, c.schedule_time
")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Scheduling</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body id="main-content" class="bg-gray-100">
    <div class="container mx-auto mt-10 p-4">
        <h1 class="text-3xl font-bold mb-6">Class Scheduling</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Scheduling Form -->
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-6">
            <h2 class="text-xl font-semibold mb-4">Schedule a New Class</h2>
            <form method="POST" class="space-y-4" action="../php/class_scheduling.php">
                <div>
                    <label for="course_id" class="block text-gray-700 text-sm font-bold mb-2">Course</label>
                    <select id="course_id" name="course_id" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name'] . ' (' . $course['course_code'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="lecturer_id" class="block text-gray-700 text-sm font-bold mb-2">Lecturer</label>
                    <select id="lecturer_id" name="lecturer_id" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <?php foreach ($lecturers as $lecturer): ?>
                            <option value="<?php echo $lecturer['id']; ?>"><?php echo htmlspecialchars($lecturer['username']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="class_date" class="block text-gray-700 text-sm font-bold mb-2">Class Date</label>
                    <input type="date" id="class_date" name="class_date" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div>
                    <label for="schedule_time" class="block text-gray-700 text-sm font-bold mb-2">Schedule Time</label>
                    <input type="time" id="schedule_time" name="schedule_time" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div>
                    <label for="room" class="block text-gray-700 text-sm font-bold mb-2">Room</label>
                    <input type="text" id="room" name="room" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Schedule Class
                    </button>
                </div>
            </form>
        </div>

        <!-- Scheduled Classes Table -->
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h2 class="text-xl font-semibold mb-4">Scheduled Classes</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-gray-300">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 border bg-gray-200">Course</th>
                            <th class="px-4 py-2 border bg-gray-200">Lecturer</th>
                            <th class="px-4 py-2 border bg-gray-200">Date</th>
                            <th class="px-4 py-2 border bg-gray-200">Schedule Time</th>
                            <th class="px-4 py-2 border bg-gray-200">Room</th>
                            <th class="px-4 py-2 border bg-gray-200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scheduled_classes as $class): ?>
                            <tr>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($class['course_name']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($class['lecturer_name']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($class['class_date']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($class['schedule_time']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($class['room']); ?></td>
                                <td class="px-4 py-2 border">
                                    <a href="edit_class.php?id=<?php echo $class['id']; ?>" class="text-blue-500 hover:text-blue-700">Edit</a> |
                                    <a href="delete_class.php?id=<?php echo $class['id']; ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to delete this class?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
