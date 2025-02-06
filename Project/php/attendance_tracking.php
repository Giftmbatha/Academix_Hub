<?php
session_start();
require '../includes/database.php';

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'lecturer') {
    $lecturer_id = $_SESSION['user_id'];
}
// Fetch classes taught by the logged-in lecturer
$classes_query = "SELECT c.id, c.course_id, c.schedule_time AS schedule, c.room, u.username AS lecturer_name, co.course_name 
                  FROM classes c
                  JOIN users u ON c.lecturer_id = u.id
                  JOIN courses co ON c.course_id = co.id
                  WHERE c.lecturer_id = ?";  // Only show classes for the logged-in lecturer
$classes_stmt = $conn->prepare($classes_query);
$classes_stmt->bind_param("i", $lecturer_id);
$classes_stmt->execute();
$classes_result = $classes_stmt->get_result();

$class_id = isset($_POST['class_id']) ? $_POST['class_id'] : 0;
$students_query = "SELECT u.id, u.username 
                   FROM enrollments e
                   JOIN users u ON e.student_id = u.id
                   JOIN classes c ON c.course_id = e.course_id
                   WHERE c.id = ? AND u.role = 'student'";
$students_stmt = $conn->prepare($students_query);
$students_stmt->bind_param("i", $class_id);
$students_stmt->execute();
$students_result = $students_stmt->get_result();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_id'], $_POST['date'])) {
    $date = $_POST['date'];
    $attendances = isset($_POST['attendance']) ? $_POST['attendance'] : [];

    $success = true;
    $conn->begin_transaction();

    foreach ($attendances as $student_id => $status) {
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, class_id, status, attendance_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $student_id, $class_id, $status, $date);
        if (!$stmt->execute()) {
            $success = false;
            break;
        }
    }

    if ($success) {
        $conn->commit();
        $message = "Attendance recorded successfully!";
        $message_type = "success";
    } else {
        $conn->rollback();
        $message = "Failed to record attendance!";
        $message_type = "error";
    }
}

// Fetch recent attendance records for the lecturer's classes
$attendance_query = "SELECT u.username AS student_name, co.course_name, a.status, a.attendance_date AS date
                     FROM attendance a
                     JOIN users u ON a.student_id = u.id
                     JOIN classes cl ON a.class_id = cl.id
                     JOIN courses co ON cl.course_id = co.id
                     WHERE cl.lecturer_id = ?
                     ORDER BY a.attendance_date DESC
                     LIMIT 10";
$attendance_stmt = $conn->prepare($attendance_query);
$attendance_stmt->bind_param("i", $lecturer_id);
$attendance_stmt->execute();
$attendance_result = $attendance_stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
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
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Attendance Tracking</h1>

       

        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Record Attendance</h2>
            <form action="../php/attendance_tracking.php" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="class_id" class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                        <select id="class_id" name="class_id" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">Select a class</option>
                            <?php while ($class = $classes_result->fetch_assoc()): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['course_name'] . ' - ' . $class['lecturer_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <input type="date" id="date" name="date" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                </div>
                <table class="w-full mb-6">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <th class="px-6 py-3">Student</th>
                            <th class="px-6 py-3">Present</th>
                            <th class="px-6 py-3">Absent</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($student = $students_result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($student['username']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="radio" id="present_<?php echo $student['id']; ?>" name="attendance[<?php echo $student['id']; ?>]" value="Present" required>
                                    <label for="present_<?php echo $student['id']; ?>" class="ml-2">Present</label>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="radio" id="absent_<?php echo $student['id']; ?>" name="attendance[<?php echo $student['id']; ?>]" value="Absent" required>
                                    <label for="absent_<?php echo $student['id']; ?>" class="ml-2">Absent</label>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div>
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-400 text-white font-bold py-2 px-4 rounded-md  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Record Attendance
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <h2 class="text-2xl font-semibold text-gray-800 p-6 bg-gray-50 border-b">Recent Attendance Records</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <th class="px-6 py-3">Student</th>
                            <th class="px-6 py-3">Class</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($record = $attendance_result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($record['student_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($record['course_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $record['status'] === 'Present' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo htmlspecialchars($record['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($record['date']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
