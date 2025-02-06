<?php
session_start();
require_once ('../includes/database.php');

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lecturer') {
    header("Location: login-signup.php");
    exit();
}

$lecturer_id = $_SESSION['user_id'];

// Handle assessment creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $class_id = $_POST['class_id'];

    // Fetch course_id based on class_id
    $stmt = $conn->prepare("SELECT course_id FROM classes WHERE id = ?");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $stmt->bind_result($course_id);
    $stmt->fetch();
    $stmt->close();

    // File upload handling
    $file = $_FILES['file'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_error = $file['error'];

    // Check for file upload errors
    if ($file_error === 0) {
        $file_destination = '../uploads/' . $file_name; // Define the upload directory

        // Move the file to the destination folder
        if (move_uploaded_file($file_tmp, $file_destination)) {
            // Insert assessment details into the database
            $stmt = $conn->prepare("INSERT INTO assessments (class_id, course_id, title, description, due_date, file_path) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissss", $class_id, $course_id, $title, $description, $due_date, $file_destination);

            if ($stmt->execute()) {
                $success_message = "Assessment and file uploaded successfully.";
            } else {
                $error_message = "Error uploading assessment.";
            }
            $stmt->close();
        } else {
            $error_message = "Error moving uploaded file.";
        }
    } else {
        $error_message = "Error uploading file.";
    }
}

// Fetch classes taught by the lecturer
$stmt = $conn->prepare("
    SELECT c.id, co.course_name, c.schedule_time, c.room 
    FROM classes c
    JOIN courses co ON c.course_id = co.id
    WHERE c.lecturer_id = ?
");
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$classes_result = $stmt->get_result();
$classes = $classes_result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Assessments</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 p-4">
        <h1 class="text-3xl font-bold mb-6">Upload Assessments</h1>

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

        <form method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4" action="../php/assessments.php">
            <div class="mb-4">
                <label for="class_id" class="block text-gray-700 text-sm font-bold mb-2">Class</label>
                <select id="class_id" name="class_id" class="w-full p-2 border border-gray-300 rounded" required>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['course_name'] . ' - ' . $class['schedule_time']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title</label>
                <input type="text" id="title" name="title" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                <textarea id="description" name="description" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="4"></textarea>
            </div>
            <div class="mb-4">
                <label for="due_date" class="block text-gray-700 text-sm font-bold mb-2">Due Date</label>
                <input type="date" id="due_date" name="due_date" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="file" class="block text-gray-700 text-sm font-bold mb-2">Upload File</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6" id="drop-area">
                    <input type="file" id="file" name="file" class="hidden" required>
                    <label for="file" class="cursor-pointer">
                        <div class="flex flex-col items-center justify-center space-y-3">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="text-gray-600">Drop your file here, or <span class="text-purple-600">Browse</span></p>
                            <p class="text-sm text-gray-500">Maximum file size 50mb</p>
                        </div>
                    </label>
                </div>
                <div id="file-name" class="mt-2 text-sm text-gray-600"></div>
            </div>
            <div>
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Upload Assessment
                </button>
            </div>
        </form>
    </div>

    <script>
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('file');
        const fileName = document.getElementById('file-name');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropArea.classList.add('border-purple-500', 'bg-purple-100');
        }

        function unhighlight(e) {
            dropArea.classList.remove('border-purple-500', 'bg-purple-100');
        }

        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            updateFileName();
        }

        fileInput.addEventListener('change', updateFileName);

        function updateFileName() {
            if (fileInput.files.length > 0) {
                fileName.textContent = `Selected file: ${fileInput.files[0].name}`;
            } else {
                fileName.textContent = '';
            }
        }
    </script>
</body>
</html>