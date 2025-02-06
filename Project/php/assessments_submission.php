<?php
session_start();
require_once '../includes/database.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login-signup.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Handle assessment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessment_id = $_POST['assessment_id'];
    $submission_text = $_POST['submission_text'];

    // Handle file upload
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['submission_file']['tmp_name'];
        $file_name = basename($_FILES['submission_file']['name']);
        $upload_dir = '../uploads/';
        $file_path = $upload_dir . $file_name;

        // Move file to upload directory
        if (move_uploaded_file($file_tmp, $file_path)) {
            // Insert into database with file path
            $stmt = $conn->prepare("INSERT INTO assessment_submissions (assessment_id, student_id, submission_text, file_path) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $assessment_id, $student_id, $submission_text, $file_path);

            if ($stmt->execute()) {
                $success_message = "Assessment submitted successfully with file.";
            } else {
                $error_message = "Error submitting assessment.";
            }
        } else {
            $error_message = "Error uploading file.";
        }
    } else {
        // Insert without file
        $stmt = $conn->prepare("INSERT INTO assessment_submissions (assessment_id, student_id, submission_text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $assessment_id, $student_id, $submission_text);

        if ($stmt->execute()) {
            $success_message = "Assessment submitted successfully.";
        } else {
            $error_message = "Error submitting assessment.";
        }
    }
}

// Fetch available assessments for the student
$stmt = $conn->prepare("
    SELECT a.id, a.title, a.description AS description, a.due_date, c.course_name
    FROM assessments a
    JOIN classes c ON a.class_id = c.id
    JOIN enrollments e ON e.course_id = c.course_id
    WHERE e.student_id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$assessments_result = $stmt->get_result();
$assessments = $assessments_result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>submission</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 p-4">
        <h1 class="text-3xl font-bold mb-6">Submit Assessments</h1>

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

        <form method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4" action="../php/assessments_submission.php">
            <div class="mb-4">
                <label for="assessment_id" class="block text-gray-700 text-sm font-bold mb-2">Assessment</label>
                <select id="assessment_id" name="assessment_id" class="w-full p-2 border border-gray-300 rounded" required>
                    <?php foreach ($assessments as $assessment): ?>
                        <option value="<?php echo $assessment['id']; ?>">
                            <?php echo $assessment['title'] . ' - ' . $assessment['course_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="submission_text" class="block text-gray-700 text-sm font-bold mb-2">Submission Text</label>
                <textarea id="submission_text" name="submission_text" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="4"></textarea>
            </div>
            <div class="mb-4">
                <label for="submission_file" class="block text-gray-700 text-sm font-bold mb-2">Upload File</label>
                <input type="file" id="submission_file" name="submission_file" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <button type="submit" class="bg-purple-600 hover:bg-purple-400 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Submit Assessment
                </button>
            </div>
        </form>
    </div>
</body>
</html>
