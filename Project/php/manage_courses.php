<?php
session_start();
require_once '../includes/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-signup.php");
    exit();
}



// Fetch the list of lecturers
$lecturers_result = $conn->query("SELECT id, username FROM users WHERE role = 'lecturer'");
$lecturers = $lecturers_result->fetch_all(MYSQLI_ASSOC);

// Handle course addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_course') {
        $course_code = $_POST['course_code'];
        $course_name = $_POST['course_name'];
        $description = $_POST['description'];
        $credits = $_POST['credits'];
        $lecturer_id = $_POST['lecturer_id'];

        $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, description, credits, lecturer_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $course_code, $course_name, $description, $credits, $lecturer_id);

        if ($stmt->execute()) {
            $success_message = "Course added successfully.";
        } else {
            $error_message = "Error adding course.";
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'edit_course') {
        $course_id = $_POST['course_id'];
        $course_code = $_POST['course_code'];
        $course_name = $_POST['course_name'];
        $description = $_POST['description'];
        $credits = $_POST['credits'];
        $lecturer_id = $_POST['lecturer_id'];

        $stmt = $conn->prepare("UPDATE courses SET course_code = ?, course_name = ?, description = ?, credits = ?, lecturer_id = ? WHERE id = ?");
        $stmt->bind_param("sssiii", $course_code, $course_name, $description, $credits, $lecturer_id, $course_id);

        if ($stmt->execute()) {
            $success_message = "Course updated successfully.";
        } else {
            $error_message = "Error updating course.";
        }
        $stmt->close();
    }
}

// Handle course deletion
if (isset($_GET['delete'])) {
    $course_id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $course_id);

    if ($stmt->execute()) {
        $success_message = "Course deleted successfully.";
    } else {
        $error_message = "Error deleting course.";
    }
    $stmt->close();
}

// Fetch all courses
$courses_result = $conn->query("SELECT * FROM courses");
$courses = $courses_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - CrestView University</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
                <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Success</p>
                    <p><?php echo $success_message; ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Error</p>
                    <p><?php echo $error_message; ?></p>
                </div>
            <?php endif; ?>
    <div class="min-h-screen flex flex-col">
        <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-8">
 
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Manage Courses</h1>

            <div class="md:flex md:space-x-8">
                <!-- Course Form -->
                <div class="md:w-1/3">
                    <div id="courseForm" class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-5 border-b border-gray-200 sm:px-6">
                            <h2 id="formTitle" class="text-lg font-medium text-gray-900">Add New Course</h2>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <form method="POST" action="../php/manage_courses.php" class="space-y-6">
                                <input type="hidden" name="action" value="add_course">
                                <div>
                                    <label for="course_code" class="block text-sm font-medium text-gray-700">Course Code</label>
                                    <input type="text" id="course_code" name="course_code" required class="mt-1 focus:ring-purple-500 focus:border-purple-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label for="course_name" class="block text-sm font-medium text-gray-700">Course Name</label>
                                    <input type="text" id="course_name" name="course_name" required class="mt-1 focus:ring-purple-500 focus:border-purple-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea id="description" name="description" rows="3" class="mt-1 focus:ring-purple-500 focus:border-purple-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                </div>
                                <div>
                                    <label for="credits" class="block text-sm font-medium text-gray-700">Credits</label>
                                    <input type="number" id="credits" name="credits" required class="mt-1 focus:ring-purple-500 focus:border-purple-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label for="lecturer_id" class="block text-sm font-medium text-gray-700">Lecturer</label>
                                    <select id="lecturer_id" name="lecturer_id" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                        <option value="">Select a Lecturer</option>
                                        <?php foreach ($lecturers as $lecturer): ?>
                                            <option value="<?php echo $lecturer['id']; ?>"><?php echo htmlspecialchars($lecturer['username']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="flex items-center justify-between">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                        Add Course
                                    </button>
                                    <button type="button" id="resetBtn" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                        Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Courses List -->
                <div class="md:w-2/3 mt-8 md:mt-0">
                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-5 border-b border-gray-200 sm:px-6">
                            <h2 class="text-lg font-medium text-gray-900">Courses List</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course Code</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credits</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lecturer</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($course['course_code']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($course['course_name']); ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($course['description']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($course['credits']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php
                                            $lecturer_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
                                            $lecturer_stmt->bind_param("i", $course['lecturer_id']);
                                            $lecturer_stmt->execute();
                                            $lecturer_stmt->bind_result($lecturer_name);
                                            $lecturer_stmt->fetch();
                                            $lecturer_stmt->close();
                                            echo htmlspecialchars($lecturer_name);
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button 
                                                class="text-indigo-600 hover:text-indigo-900 mr-2"
                                                onclick="editCourse(<?php echo htmlspecialchars(json_encode($course)); ?>)"
                                            >
                                                Edit
                                            </button>
                                            <a href="../php/manage_courses.php?delete=<?php echo $course['id']; ?>" 
                                                class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Are you sure you want to delete this course?');">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const courseForm = document.getElementById('courseForm');
        const resetBtn = document.getElementById('resetBtn');
        const formTitle = document.getElementById('formTitle');

        function resetForm() {
            formTitle.textContent = 'Add New Course';
            document.getElementById('course_code').value = '';
            document.getElementById('course_name').value = '';
            document.getElementById('description').value = '';
            document.getElementById('credits').value = '';
            document.getElementById('lecturer_id').value = '';
        }

        resetBtn.addEventListener('click', resetForm);

        function editCourse(course) {
            formTitle.textContent = 'Edit Course';
            document.getElementById('course_code').value = course.course_code;
            document.getElementById('course_name').value = course.course_name;
            document.getElementById('description').value = course.description;
            document.getElementById('credits').value = course.credits;
            document.getElementById('lecturer_id').value = course.lecturer_id;
            courseForm.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>