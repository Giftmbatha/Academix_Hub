<?php
session_start();
require_once '../includes/database.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-signup.php");
    exit();
}

// Handle form submission for adding or updating users
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role']; // Admin can set role for the user
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

    if ($user_id) {
        // Update user
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $username, $email, $password, $role, $user_id);
    } else {
        // Add new user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $password, $role);
    }

    if ($stmt->execute()) {
        $success_message = $user_id ? "User updated successfully." : "User added successfully.";
    } else {
        $error_message = "Error in processing request.";
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $success_message = "User deleted successfully.";
    } else {
        $error_message = "Error deleting user.";
    }
}

// Fetch all users
$result = $conn->query("SELECT id, username, email, role FROM users");
$users = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - CrestView University</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
   <div class="container mx-auto mt-10 p-6">
        <h1 class="text-4xl font-semibold text-gray-800 mb-8">Manage Users</h1>

        <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-8">
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

            <div class="md:flex md:space-x-8">
                <!-- User Form -->
                <div class="md:w-1/3">
                    <div id="userForm" class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-5 border-b border-gray-200 sm:px-6">
                            <h2 id="formTitle" class="text-lg font-medium text-gray-900">Add New User</h2>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <form method="POST" class="space-y-6">
                                <input type="hidden" name="user_id" id="user_id">
                                <div>
                                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                    <input type="text" id="username" name="username" required class="mt-1 focus:ring-purple-500 focus:border-purple-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" id="email" name="email" required class="mt-1 focus:ring-purple-500 focus:border-purple-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                    <input type="password" id="password" name="password" required class="mt-1 focus:ring-purple-500 focus:border-purple-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                                    <select id="role" name="role" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                        <option value="student">Student</option>
                                        <option value="lecturer">Lecturer</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                                <div class="flex items-center justify-between">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                        Save User
                                    </button>
                                    <button type="button" id="resetBtn" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                        Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Users List -->
                <div class="md:w-2/3 mt-8 md:mt-0">
                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-5 border-b border-gray-200 sm:px-6">
                            <h2 class="text-lg font-medium text-gray-900">Users List</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button 
                                                class="text-indigo-600 hover:text-indigo-900 mr-2"
                                                onclick="editUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['role']); ?>')"
                                            >
                                                Edit
                                            </button>
                                            <a href="?delete=<?php echo $user['id']; ?>" 
                                                class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Are you sure you want to delete this user?');">
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
        const userForm = document.getElementById('userForm');
        const resetBtn = document.getElementById('resetBtn');
        const formTitle = document.getElementById('formTitle');

        function resetForm() {
            formTitle.textContent = 'Add New User';
            document.getElementById('user_id').value = '';
            document.getElementById('username').value = '';
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            document.getElementById('role').value = 'student';
        }

        resetBtn.addEventListener('click', resetForm);

        function editUser(id, username, email, role) {
            formTitle.textContent = 'Edit User';
            document.getElementById('user_id').value = id;
            document.getElementById('username').value = username;
            document.getElementById('email').value = email;
            document.getElementById('password').value = '';
            document.getElementById('role').value = role;
            userForm.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>