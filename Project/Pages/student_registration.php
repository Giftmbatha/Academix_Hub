<?php
session_start();
require_once('../includes/database.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login-signup.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_name = $_POST['student_name'];
    $student_email = $_POST['student_email'];
    $student_course = $_POST['student_course'];

    // Validate inputs and insert into the database
    if ($student_name && $student_email && $student_course) {
        $stmt = $conn->prepare("INSERT INTO students (name, email, course) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $student_name, $student_email, $student_course);

        if ($stmt->execute()) {
            $message = "Student registered successfully!";
        } else {
            $message = "Failed to register student!";
        }
        $stmt->close();
    } else {
        $message = "Please fill all the fields!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <!-- Content -->
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Register a New Student</h1>

        <?php if (isset($message)): ?>
        <div class="bg-<?php echo strpos($message, 'successfully') !== false ? 'green' : 'red'; ?>-500 text-white p-4 mb-6 rounded">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <form action="student_registration.php" method="POST">
            <div class="mb-4">
                <label for="student_name" class="block text-gray-700">Student Name</label>
                <input type="text" id="student_name" name="student_name" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="student_email" class="block text-gray-700">Student Email</label>
                <input type="email" id="student_email" name="student_email" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="student_course" class="block text-gray-700">Student Course</label>
                <input type="text" id="student_course" name="student_course" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Register</button>
            </div>
        </form>
    </div>
</body>
</html>
