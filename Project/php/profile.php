<?php
session_start();
require_once '../includes/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login-signup.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Define the directory where you want to store the uploaded images
$upload_dir = '../uploads/profile_photos'; // Adjust this path as needed

// Handle profile photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
    $target_file = $upload_dir . basename($_FILES['profile_photo']['name']);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a valid image type
    $check = getimagesize($_FILES['profile_photo']['tmp_name']);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        $error_message = "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size (max 2MB)
    if ($_FILES['profile_photo']['size'] > 2000000) {
        $error_message = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        $error_message = "Sorry, only JPG, JPEG, and PNG files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $error_message = "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
            // Update profile photo in the database
            $stmt = $conn->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
            $stmt->bind_param("si", $target_file, $user_id);
            if ($stmt->execute()) {
                $success_message = "Profile photo uploaded successfully.";
            } else {
                $error_message = "Sorry, there was an error updating your profile photo.";
            }
        } else {
            $error_message = "Sorry, there was an error uploading your file.";
        }
    }
}


// Fetch user data
$stmt = $conn->prepare("SELECT username, email, role, profile_photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - CrestView University</title>
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
        <h1 class="text-3xl font-bold text-gray-800 mb-8">User Profile</h1>

        <!-- PHP: Display success message -->
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p class="font-bold">Success</p>
                <p><?php echo $success_message; ?></p>
            </div>
        <?php endif; ?>

        <!-- PHP: Display error message -->
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">Error</p>
                <p><?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Profile Photo Section -->
            <div class="md:col-span-1 bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Profile Photo</h2>
                <div class="flex flex-col items-center">
                    <?php if ($user['profile_photo']): ?>
                        <img src="<?php echo $user['profile_photo']; ?>" alt="Profile Photo" class="w-32 h-32 rounded-full object-cover mb-4">
                    <?php else: ?>
                        <div class="w-32 h-32 rounded-full bg-gray-300 flex items-center justify-center mb-4">
                            <span class="text-4xl text-gray-600">
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <form action="../php/profile.php" method="POST" enctype="multipart/form-data" class="w-full">
                        <div class="flex items-center justify-center w-full">
                            <label for="profile_photo" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                    </svg>
                                    <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                    <p class="text-xs text-gray-500">PNG, JPG or JPEG (MAX. 2MB)</p>
                                </div>
                                <input id="profile_photo" name="profile_photo" type="file" class="hidden" accept="image/png, image/jpeg" />
                            </label>
                        </div>
                        <button type="submit" class="mt-4 w-full bg-purple-600 hover:bg-purple-400 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                            Upload New Photo
                        </button>
                    </form>
                </div>
            </div>

            <!-- Profile Information Section -->
            <div class="md:col-span-2 bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Profile Information</h2>
                <form action="../php/update_profile.php" method="POST" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo $user['username']; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <input type="text" id="role" name="role" value="<?php echo ucfirst($user['role']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" readonly>
                    </div>
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700">New Password (leave blank to keep current)</label>
                        <input type="password" id="new_password" name="new_password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </div>
                    <div class="flex items-center justify-end">
                        <button type="submit" class="bg-purple-600 hover:bg-purple-400 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    

    <script>
        // Preview uploaded image
        document.getElementById('profile_photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.querySelector('img') || document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Profile Photo';
                    img.className = 'w-32 h-32 rounded-full object-cover mb-4';
                    const container = document.querySelector('form').parentNode;
                    container.insertBefore(img, container.firstChild);
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>