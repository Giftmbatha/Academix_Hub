<?php
session_start();
require_once '../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login-signup.php");
    exit();
}

// Handle announcement creation (for admins and lecturers)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_announcement']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'lecturer')) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO communications (sender_id, recipient_id, title, message) VALUES (?, NULL, ?, ?)");
    $stmt->bind_param("iss", $user_id, $title, $content);
    
    if ($stmt->execute()) {
        $success_message = "Announcement created successfully.";
    } else {
        $error_message = "Error creating announcement.";
    }
}

// Fetch announcements
$stmt = $conn->prepare("
    SELECT c.id, c.title, c.message AS content, c.sent_at AS created_at, u.username, u.role
    FROM communications c
    JOIN users u ON c.sender_id = u.id
    WHERE c.recipient_id IS NULL
    ORDER BY c.sent_at DESC
    LIMIT 10
");
$stmt->execute();
$result = $stmt->get_result();
$announcements = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Communication Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-800">Academic Communication Center</h1>

        </div>
    

    <div class="container mx-auto px-4 py-8">
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="bg-gray-100 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Recent Announcements</h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <?php if (empty($announcements)): ?>
                            <p class="p-6 text-gray-500">No announcements yet.</p>
                        <?php else: ?>
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="p-6 hover:bg-gray-50 transition duration-150 ease-in-out">
                                    <h3 class="font-bold text-lg text-gray-800 mb-2"><?php echo htmlspecialchars($announcement['title']); ?></h3>
                                    <p class="text-gray-600 text-sm mb-3">
                                        Posted by <?php echo htmlspecialchars($announcement['username']); ?> 
                                        <span class="text-gray-500">(<?php echo htmlspecialchars($announcement['role']); ?>)</span> 
                                        on <?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($announcement['created_at']))); ?>
                                    </p>
                                    <p class="text-purple-600"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'lecturer'): ?>
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="bg-gray-100 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-800">Create Announcement</h2>
                        </div>
                        <form method="POST" class="p-6 space-y-4" action="../php/communication.php">
                            <input type="hidden" name="create_announcement" value="1">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                <input type="text" id="title" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                                <textarea id="content" name="content" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" rows="4"></textarea>
                            </div>
                            <div>
                                <button type="submit" class="w-full bg-purple-600 text-white font-semibold py-2 px-4 rounded-md hover:bg-purple-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150 ease-in-out">
                                    Post Announcement
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="bg-gray-100 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Quick Links</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <a href="#" class="block text-indigo-600 hover:text-indigo-800 transition duration-150 ease-in-out">
                            Course Materials
                        </a>
                        <a href="#" class="block text-indigo-600 hover:text-indigo-800 transition duration-150 ease-in-out">
                            Academic Calendar
                        </a>
                        <a href="#" class="block text-indigo-600 hover:text-indigo-800 transition duration-150 ease-in-out">
                            Grade Center
                        </a>
                        <a href="#" class="block text-indigo-600 hover:text-indigo-800 transition duration-150 ease-in-out">
                            Student Resources
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>