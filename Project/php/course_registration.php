<?php
session_start();
require_once '../includes/database.php';


// Fetch available courses and their classes
$stmt = $conn->prepare("SELECT c.id AS course_id, c.course_name, c.course_code, cl.id AS class_id, cl.room, cl.schedule_time
                        FROM courses c
                        LEFT JOIN classes cl ON c.id = cl.course_id
                        WHERE c.id NOT IN (SELECT course_id FROM enrollments WHERE student_id = ?)");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$available_courses = [];
while ($row = $result->fetch_assoc()) {
    $course_id = $row['course_id'];
    if (!isset($available_courses[$course_id])) {
        $available_courses[$course_id] = [
            'course_name' => $row['course_name'],
            'course_code' => $row['course_code'],
            'classes' => []
        ];
    }
    $available_courses[$course_id]['classes'][] = [
        'class_id' => $row['class_id'],
        'room' => $row['room'],
        'schedule_time' => $row['schedule_time']
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'], $_POST['class_id'])) {
    $course_id = $_POST['course_id'];
    $class_id = $_POST['class_id'];
    $student_id = $_SESSION['user_id'];
    $enrollment_date = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id, class_id, enrollment_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $student_id, $course_id, $class_id, $enrollment_date);

    if ($stmt->execute()) {
        $success_message = "Successfully registered for the course.";
    } else {
        $error_message = "Error registering for the course.";
    }
}


$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 p-4">
        <h1 class="text-3xl font-bold mb-6">Course Registration</h1>
        
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
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($available_courses as $course_id => $course): ?>
        <div class="border rounded p-4">
            <h3 class="font-bold"><?php echo htmlspecialchars($course['course_name']); ?></h3>
            <p class="text-gray-600"><?php echo htmlspecialchars($course['course_code']); ?></p>
            
            <?php if (!empty($course['classes'])): ?>
                <form method="POST" class="mt-4" action="../php/course_registration.php">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    <label for="class_id_<?php echo $course_id; ?>" class="block text-sm font-medium text-gray-700">Select Class:</label>
                    <select id="class_id_<?php echo $course_id; ?>" name="class_id" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <?php foreach ($course['classes'] as $class): ?>
                            <option value="<?php echo $class['class_id']; ?>">
                                Room: <?php echo htmlspecialchars($class['room']); ?> - Time: <?php echo htmlspecialchars($class['schedule_time']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-400 text-white font-bold py-2 px-4 rounded mt-4">
                        Register
                    </button>
                </form>
            <?php else: ?>
                <p class="text-red-500 mt-2">No classes available for this course.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>