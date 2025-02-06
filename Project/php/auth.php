<?php
include_once ('../includes/config.php');
include_once('../includes/database.php');


header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

function login($username, $password, $role) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, username, role, password FROM users WHERE username = ? AND role = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            unset($user['password']);
            return ['success' => true, 'user' => $user, 'redirect' => 'base_dashboard.html'];
        }
    }
    
    return ['success' => false, 'message' => 'Invalid credentials'];
}

function signup($username, $password, $email, $role) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Username or email already exists'];
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $hashed_password, $email, $role);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Account created successfully'];
    } else {
        return ['success' => false, 'message' => 'Error creating account'];
    }
}

switch ($action) {
    case 'login':
        echo json_encode(login($data['username'], $data['password'], $data['role']));
        break;
    case 'signup':
        echo json_encode(signup($data['username'], $data['password'], $data['email'], $data['role']));
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>