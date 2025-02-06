<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);


include('../includes/database.php');

// Assume user is logged in and we have their ID
$user_id = $_SESSION['user_id'] ?? 1; // Replace with actual session management

// Fetch all users except the current user
$users_query = "SELECT id, username, profile_photo FROM users WHERE id != ?";
$users_stmt = $conn->prepare($users_query);
$users_stmt->bind_param("i", $user_id);
$users_stmt->execute();
$users_result = $users_stmt->get_result();

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && isset($_POST['recipient_id'])) {
    $message = $_POST['message'];
    $recipient_id = $_POST['recipient_id'];
    
    $insert_query = "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iis", $recipient_id, $user_id, $message);
    if ($insert_stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Message sent successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to send message: ' . $insert_stmt->error]);
    }
    exit;
}

// Fetch messages for a specific chat
function fetchMessages($conn, $user_id, $other_user_id) {
    $query = "SELECT * FROM messages 
              WHERE (incoming_msg_id = ? AND outgoing_msg_id = ?)
              OR (incoming_msg_id = ? AND outgoing_msg_id = ?)
              ORDER BY created_at ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $user_id, $other_user_id, $other_user_id, $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Handle AJAX requests for fetching messages
if (isset($_GET['action']) && $_GET['action'] === 'get_messages' && isset($_GET['user_id'])) {
    $other_user_id = $_GET['user_id'];
    $messages = fetchMessages($conn, $user_id, $other_user_id);
    $output = '';

    while ($message = $messages->fetch_assoc()) {
        $isOutgoing = $message['outgoing_msg_id'] == $user_id;
        $alignClass = $isOutgoing ? 'ml-auto' : 'mr-auto';
        $bgClass = $isOutgoing ? 'bg-purple-600 text-white' : 'bg-gray-300';
        
        $output .= "<div class='flex mb-4'>";
        $output .= "<div class='max-w-[70%] $alignClass $bgClass rounded-lg p-2'>";
        $output .= htmlspecialchars($message['msg']);
        $output .= "</div>";
        $output .= "</div>";
    }

    echo $output;
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educational Chat Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .chat-height {
            height: calc(100vh - 2rem);
        }
        .message-input {
            height: 3rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden chat-height">
            <div class="flex h-full">
                <!-- Users list -->
                <div class="w-1/3 border-r border-gray-200 flex flex-col">
                    <div class="bg-gray-100 p-4 flex items-center justify-between">
                        <img src="/api/placeholder/40/40" alt="" class="w-10 h-10 rounded-full">
                        <div class="flex space-x-4">
                            <button class="text-gray-600 hover:text-gray-800">
                                <i class="fas fa-circle-notch"></i>
                            </button>
                            <button class="text-gray-600 hover:text-gray-800">
                                <i class="fas fa-comment-alt"></i>
                            </button>
                            <button class="text-gray-600 hover:text-gray-800">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                    <div class="bg-white p-2">
                        <input type="text" placeholder="Search or start new chat" class="w-full p-2 bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <ul class="overflow-y-auto flex-1">
                        <?php while ($user = $users_result->fetch_assoc()): ?>
                            <li class="user-item p-3 hover:bg-gray-100 cursor-pointer transition duration-200 ease-in-out" data-user-id="<?= $user['id'] ?>">
                                <div class="flex items-center">
                                    <img src="<?= $user['profile_photo'] ?? '/api/placeholder/40/40' ?>" alt="<?= $user['username'] ?>" class="w-12 h-12 rounded-full mr-3">
                                    <div class="flex-1">
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium"><?= $user['username'] ?></span>
                                            <span class="text-xs text-gray-500">3:45 PM</span>
                                        </div>
                                        <p class="text-sm text-gray-600 truncate">Last message preview...</p>
                                    </div>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                
                <!-- Chat area -->
                <div class="w-2/3 flex flex-col">
                    <div id="chat-header" class="bg-gray-100 p-4 flex items-center justify-between border-b border-gray-200">
                        <div class="flex items-center">
                            <img src="/api/placeholder/40/40" alt="" class="w-10 h-10 rounded-full mr-3">
                            <div>
                                <h2 class="font-bold">Chat User</h2>
                                <p class="text-xs text-gray-600">Online</p>
                            </div>
                        </div>
                        <div class="flex space-x-4">
                            <button class="text-gray-600 hover:text-gray-800">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="text-gray-600 hover:text-gray-800">
                                <i class="fas fa-paperclip"></i>
                            </button>
                            <button class="text-gray-600 hover:text-gray-800">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                    <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                        <!-- Messages will be loaded here -->
                    </div>
                    <div class="bg-gray-100 p-4">
                        <form id="message-form" class="flex items-center">
                            <button type="button" class="text-gray-600 hover:text-gray-800 mr-4">
                                <i class="far fa-smile"></i>
                            </button>
                            <input type="text" id="message-input" class="flex-1 rounded-full border border-gray-300 p-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent message-input" placeholder="Type a message">
                            <button type="submit" class="ml-4 bg-purple-700 text-white p-2 rounded-full hover:bg-purple-600 transition duration-200 ease-in-out">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let currentChatUser = null;

            $('.user-item').click(function() {
                currentChatUser = $(this).data('user-id');
                loadMessages(currentChatUser);
                updateChatHeader($(this).find('.font-medium').text());
                $('.user-item').removeClass('bg-gray-100');
                $(this).addClass('bg-gray-100');
            });

            function updateChatHeader(username) {
                $('#chat-header h2').text(username);
            }

            function loadMessages(userId) {
                $.ajax({
                    url: 'chats.php',
                    method: 'GET',
                    data: { action: 'get_messages', user_id: userId },
                    success: function(response) {
                        $('#chat-messages').html(response);
                        $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error loading messages:", error);
                        alert("Failed to load messages. Please try again.");
                    }
                });
            }

            $('#message-form').submit(function(e) {
                e.preventDefault();
                if (!currentChatUser) {
                    alert("Please select a user to chat with.");
                    return;
                }

                let message = $('#message-input').val();
                if (message.trim() === '') return;

                $.ajax({
                    url: 'chats.php',
                    method: 'POST',
                    data: {
                        message: message,
                        recipient_id: currentChatUser
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#message-input').val('');
                            loadMessages(currentChatUser);
                        } else {
                            console.error("Error sending message:", response.message);
                            alert("Failed to send message. Please try again.");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error sending message:", error);
                        alert("Failed to send message. Please try again.");
                    }
                });
            });
        });
    </script>
</body>
</html>