<?php
session_start();
$user_id = $_SESSION['user_id'];

$data = json_decode(file_get_contents('php://input'), true);
$recipient_id = $data['recipient_id'];
$message = $data['msg'];

$conn = new mysqli('localhost', 'root', '', 'student_management_system');

// Insert the message into the database
$sql = "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iis', $recipient_id, $user_id, $message);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$conn->close();
?>
