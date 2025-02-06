<?php
session_start();
$user_id = $_SESSION['user_id'];

$data = json_decode(file_get_contents('php://input'), true);
$recipient_id = $data['recipient_id'];

$conn = new mysqli('localhost', 'root', '', 'student_management_system');

// Fetch messages between the logged-in user and the selected recipient
$sql = "SELECT * FROM messages 
        WHERE (incoming_msg_id = ? AND outgoing_msg_id = ?) 
        OR (incoming_msg_id = ? AND outgoing_msg_id = ?) 
        ORDER BY msg_id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iiii', $user_id, $recipient_id, $recipient_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode(['messages' => $messages]);

$conn->close();
?>
