<?php
// Start session and include database connection
session_start();
require '../includes/database.php';

// Check if the form is submitted and the user is a teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title']) && $_SESSION['role'] === 'lecturer') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $topic = $_POST['topic'];
    $course_id = $_POST['course_id'];
    $level = $_POST['level'];
    $uploaded_by = $_SESSION['user_id'];

    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file_name = basename($_FILES['file']['name']);
        $file_path = "uploads/resources/" . $file_name;
        $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

        // Ensure the uploads directory exists
        if (!is_dir('uploads/resources')) {
            mkdir('uploads/resources', 0777, true);
        }

        if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            $stmt = $conn->prepare("INSERT INTO resources (title, description, file_path, file_type, topic, course_id, level, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssisi", $title, $description, $file_path, $file_type, $topic, $course_id, $level, $uploaded_by);
            $stmt->execute();
            $upload_message = "Resource uploaded successfully!";
        } else {
            $upload_message = "Failed to upload file.";
        }
    } else {
        $upload_message = "No file selected or file upload error.";
    }
}

// Fetch resources to display
$resources_query = "SELECT * FROM resources";
$resources_result = $conn->query($resources_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resource Library</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">

        <!-- Upload Resource Form -->
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Upload New Resource</h2>
        
        <?php if (isset($upload_message)) echo "<p>$upload_message</p>"; ?>

        <form action="resources.php" method="POST" enctype="multipart/form-data" class="space-y-4 bg-white p-6 rounded shadow-md">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Resource Title:</label>
                <input type="text" id="title" name="title" required class="mt-1 block w-full rounded-md border-gray-300">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description:</label>
                <textarea id="description" name="description" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
            </div>

            <div>
                <label for="topic" class="block text-sm font-medium text-gray-700">Topic/Category:</label>
                <input type="text" id="topic" name="topic" required class="mt-1 block w-full rounded-md border-gray-300">
            </div>

            <div>
                <label for="course" class="block text-sm font-medium text-gray-700">Course:</label>
                <input type="number" id="course" name="course_id" required class="mt-1 block w-full rounded-md border-gray-300">
            </div>

            <div>
                <label for="level" class="block text-sm font-medium text-gray-700">Student Level:</label>
                <input type="text" id="level" name="level" class="mt-1 block w-full rounded-md border-gray-300">
            </div>

            <div>
                <label for="file" class="block text-sm font-medium text-gray-700">Upload File:</label>
                <input type="file" id="file" name="file" class="mt-1 block w-full">
            </div>

            <div>
                <button type="submit" class="bg-purple-600 text-white font-bold py-2 px-4 rounded">
                    Upload Resource
                </button>
            </div>
        </form>

        <!-- Display Resources -->
        <h2 class="text-2xl font-semibold text-gray-800 mt-12 mb-6">Available Resources</h2>
        <ul class="space-y-4">
            <?php while ($resource = $resources_result->fetch_assoc()): ?>
                <li class="bg-white p-4 rounded shadow-md">
                    <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($resource['title']); ?></h3>
                    <p><?php echo htmlspecialchars($resource['description']); ?></p>
                    <p><strong>Topic:</strong> <?php echo htmlspecialchars($resource['topic']); ?></p>
                    <p><strong>Level:</strong> <?php echo htmlspecialchars($resource['level']); ?></p>
                    <a href="<?php echo htmlspecialchars($resource['file_path']); ?>" target="_blank" class="text-blue-600 hover:underline">Download</a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>
