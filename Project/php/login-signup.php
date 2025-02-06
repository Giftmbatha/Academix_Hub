<?php
include_once('../includes/database.php');

$role = isset($_GET['role']) ? $_GET['role'] : '';
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';

    // Basic validation
    if (empty($username) || empty($password) || empty($role)) {
        $error_message = "All fields except email are required.";
    } else {
        // Truncate role if necessary
        $role = substr($role, 0, 10); // Adjust this value to match your database schema

        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $role);

        // Execute the statement
        if ($stmt->execute()) {
            $success_message = "User registered successfully with role: $role";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/tailwind.css">
    <title>Login/Sign Up Page</title>
    <link rel="icon" type="image/jpg" href="/images/crestview-university-high-resolution-logo-transparent.png" alt="logo">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <style>
        .or { text-align: center; margin: 15px 0; }
        .or span { padding: 0 10px; color: #666; }
        .icons { text-align: center; font-size: 24px; margin-bottom: 15px; }
        .icons i { margin: 0 10px; cursor: pointer; }
        .links { text-align: center; margin-top: 15px; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <!-- Container -->
    <div class="w-full max-w-4xl flex bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Left Side (Logo Section) -->
        <div class="w-1/2 bg-gradient-to-r from-purple-400 to-purple-600 flex flex-col items-center justify-center p-10">
            <div class="text-center">
                <img src="/images/crestview-university-high-resolution-logo-white-transparent.png" alt="University Logo" class="w-32 h-32 mb-6 mx-auto">
                <h1 class="text-white text-3xl font-semibold">CrestView University</h1>
                <p class="text-white text-sm mt-2">Education is forever</p>
            </div>
        </div>

        <!-- Right Side (Form Section) -->
        <div class="w-1/2 p-10">
            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <!-- Sign In Form -->
            <div id="signInForm">
                <img src="/images/crestview-university-high-resolution-logo-black-transparent.png" alt="University Logo" class="w-32 h-32 mb-6 mx-auto">
                <h2 class="text-2xl font-bold mb-6 text-center">Sign In</h2>

                <form method="post" action="/php/register.php">
                    <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
                    <div class="mb-4">
                        <label for="username" class="block text-gray-600 mb-2">Full Name</label>
                        <input type="text" name="username" id="username" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" required>
                    </div>
                    <div class="mb-6">
                        <label for="password" class="block text-gray-600 mb-2">Password</label>
                        <input type="password" name="password" id="password" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" required>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-purple-400 to-purple-600 text-white font-semibold py-2 rounded hover:bg-purple-700 transition duration-200">Login</button>
                </form>

                <p class="or">
                    <span>----------or----------</span>
                </p>

                <div class="icons">
                    <i class="fab fa-google text-red-500 hover:text-red-600 transition-colors duration-200" onclick="signInWithGoogle()"></i>
                    <i class="fab fa-microsoft text-blue-500 hover:text-blue-600 transition-colors duration-200" onclick="signInWithMicrosoft()"></i>
                </div>

                <div class="links">
                    <span>Don't Have an Account?</span>
                    <a href="#" class="text-purple-600 hover:underline ml-1">Signup</a>
                </div>
            </div>

            <!-- Sign Up Form -->
            <div id="signUpForm" class="hidden">
                <img src="/images/crestview-university-high-resolution-logo-black-transparent.png" alt="University Logo" class="w-32 h-32 mb-6 mx-auto">
                <h2 class="text-2xl font-bold mb-6 text-center">Sign Up</h2>

                <form method="post" action="login-signup.php">
                    <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
                    <?php if ($role !== 'Admin') : ?>
                        <div class="mb-4">
                            <label for="email" class="block text-gray-600 mb-2">Email Address</label>
                            <input type="email" name="email" id="email" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" placeholder="example@gmail.com" required>
                        </div>
                    <?php endif; ?>
                    <div class="mb-4">
                        <label for="username" class="block text-gray-600 mb-2">Full Name</label>
                        <input type="text" name="username" id="username" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" placeholder="John Doe" required>
                    </div>
                    <div class="mb-6">
                        <label for="password" class="block text-gray-600 mb-2">Password</label>
                        <input type="password" name="password" id="password" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-purple-400 to-purple-600 text-white font-semibold py-2 rounded hover:bg-purple-700 transition duration-200">Sign Up</button>
                </form>

                <p class="or">
                    <span>----------or----------</span>
                </p>

                <div class="icons">
                    <i class="fab fa-google text-red-500 hover:text-red-600 transition-colors duration-200" onclick="signUpWithGoogle()"></i>
                    <i class="fab fa-microsoft text-blue-500 hover:text-blue-600 transition-colors duration-200" onclick="signUpWithMicrosoft()"></i>
                </div>

                <div class="links">
                    <span>Already Have an Account?</span>
                    <a href="#" class="text-purple-600 hover:underline ml-1">Login</a>
                </div>
            </div>
        </div>
    </div>

    <script src="/js/auth.js"></script>
    <script>
        // Toggle between Sign In and Sign Up forms
        const signInForm = document.getElementById('signInForm');
        const signUpForm = document.getElementById('signUpForm');

        document.querySelectorAll('a[href="#"]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                signInForm.classList.toggle('hidden');
                signUpForm.classList.toggle('hidden');
            });
        });

        // Placeholder functions for social login
        function signInWithGoogle() {
            alert('Sign in with Google clicked');
            // Implement Google sign-in logic here
        }

        function signInWithMicrosoft() {
            alert('Sign in with Microsoft clicked');
            // Implement Microsoft sign-in logic here
        }

        function signUpWithGoogle() {
            alert('Sign up with Google clicked');
            // Implement Google sign-up logic here
        }

        function signUpWithMicrosoft() {
            alert('Sign up with Microsoft clicked');
            // Implement Microsoft sign-up logic here
        }
    </script>
</body>
</html>