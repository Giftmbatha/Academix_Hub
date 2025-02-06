<?php
include '../includes/database.php';

// Start output as an empty string
$output = "";

while ($user_row = mysqli_fetch_assoc($query)) {
    // Get the latest message between the current user and the logged-in user
    $sql = "SELECT * FROM messages 
            WHERE (incoming_msg_id = {$user_row['id']} OR outgoing_msg_id = {$user_row['id']}) 
            AND (outgoing_msg_id = {$outgoing_id} OR incoming_msg_id = {$outgoing_id}) 
            ORDER BY msg_id DESC LIMIT 1";
    $message_query = mysqli_query($conn, $sql);
    
    // Default message if none are found
    if (mysqli_num_rows($message_query) > 0) {
        $message_row = mysqli_fetch_assoc($message_query);
        $result = $message_row['msg'];
    } else {
        $result = "No message available";
    }

    // Shorten the message to 28 characters
    $msg = (strlen($result) > 28) ? substr($result, 0, 28) . '...' : $result;

    // Check if the latest message is from the logged-in user
    $you = "";
    if (isset($message_row['outgoing_msg_id'])) {
        $you = ($outgoing_id == $message_row['outgoing_msg_id']) ? "You: " : "";
    }

    // Determine the online status of the user
    $offline = ($user_row['status'] == "Offline Now") ? "offline" : "";

    // Hide your own user row
    $hid_me = ($outgoing_id == $user_row['id']) ? "hide" : "";

    // Append the user details and the latest message to the output
    $output .= '
        <a href="/php/chat.php?user_id=' . $user_row['id'] . '" class="' . $hid_me . '">
            <div class="content">
                <img src="uploads/' . $user_row['profile_photo'] . '" alt="User Photo">
                <div class="details">
                    <span>' . $user_row['username'] . '</span>
                    <p>' . $you . $msg . '</p>
                </div>
            </div>
            <div class="status-dot ' . $offline . '"></div>
        </a>';
}

?>