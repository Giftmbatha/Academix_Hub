<?php
require_once '../includes/database.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['chat_id']) || !isset($_POST['message'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized or missing required data']);
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_id = $_POST['chat_id'];
$message = $_POST['message'];

// Verify user is a member of the chat
$query = "SELECT 1 FROM chat_members WHERE user_id = ? AND chat_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $chat_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'User is not a member of this chat']);
    exit();
}

// Insert the new message
$query = "INSERT INTO messages (chat_id, user_id, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iis", $chat_id, $user_id, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to send message']);
}