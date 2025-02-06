<?php
include_once('../includes/database.php');
session_start();

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
        $error_message = "Both username and password are required.";
    } else {
        // Prepare and execute the query
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if the user exists
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Verify the password
                if (password_verify($password, $user['password'])) {
                    // Password is correct, log the user in
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $user['role'];

                    // Set success message
                    $success_message = "Login successful. Redirecting...";

                    // Redirect to the user's dashboard based on their role
                    header("Refresh: 2; URL=/index.php");
                    exit;
                } else {
                    $error_message = "Invalid password.";
                }
            } else {
                $error_message = "No user found with that username.";
            }
            $stmt->close();
        } else {
            $error_message = "Database error. Please try again later.";
        }
    }
}
?>
