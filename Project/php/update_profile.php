<?php
session_start();
require '../includes/database.php';  // Update this path as needed

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die('Access Denied');
}

$user_id = $_SESSION['user_id'];

// Initialize variables
$new_password = null;
$confirm_password = null;
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$message = '';
$message_type = '';

// Fetch the current user profile
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? null;
    $confirm_password = $_POST['confirm_password'] ?? null;

    // Validate required fields
    if (empty($username) || empty($email)) {
        $message = "Username and email cannot be empty.";
        $message_type = "error";
    } else {
        // Check if a password change is requested
        if (!empty($new_password) && !empty($confirm_password)) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            } else {
                $message = "New password and confirmation do not match.";
                $message_type = "error";
            }
        }

        // Update user profile if there are no errors
        if (empty($message)) {
            $update_query = "UPDATE users SET username = ?, email = ?";
            $params = [$username, $email];
            $types = "ss";

            // Add password to query if it was changed
            if (!empty($hashed_password)) {
                $update_query .= ", password = ?";
                $params[] = $hashed_password;
                $types .= "s";
            }
            $update_query .= " WHERE id = ?";
            $params[] = $user_id;
            $types .= "i";

            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param($types, ...$params);

            if ($update_stmt->execute()) {
                $message = "Profile updated successfully!";
                $message_type = "success";
                $_SESSION['username'] = $username;  // Update session username if changed
            } else {
                $message = "Failed to update profile. Please try again.";
                $message_type = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

<?php if (isset($success_message)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p class="font-bold">Success</p>
                <p><?php echo $success_message; ?></p>
            </div>
        <?php endif; ?>
    
</body>
</html>
 