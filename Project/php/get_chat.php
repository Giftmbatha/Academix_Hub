<?php
session_start();
if(isset($_SESSION['user_id'])){
    include "../includes/database.php";
    $outgoing_id = $_SESSION['user_id'];
    $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
    $output = "";

    // Fetch chat messages between outgoing and incoming users
    $sql = "SELECT * FROM messages 
            LEFT JOIN users ON users.id = messages.outgoing_msg_id
            WHERE (outgoing_msg_id = {$outgoing_id} AND incoming_msg_id = {$incoming_id})
            OR (outgoing_msg_id = {$incoming_id} AND incoming_msg_id = {$outgoing_id})
            ORDER BY msg_id ASC";
    
    $query = mysqli_query($conn, $sql);
    
    // Fetch the incoming user's profile info
    $user_sql = "SELECT * FROM users WHERE id = '$incoming_id'";
    $user_query = mysqli_query($conn, $user_sql) or die('Query failed: ' . mysqli_error($conn));
    $row2 = mysqli_fetch_assoc($user_query);
    
    if(mysqli_num_rows($query) > 0){
        while($row = mysqli_fetch_assoc($query)){
            // Outgoing messages (from the current logged-in user)
            if($row['outgoing_msg_id'] === $outgoing_id){
                if(empty($row['msg']) && !empty($row['msg_img'])){ // If the message is an image
                    $output .= '<div class="chat outgoing">
                                    <div class="details">
                                        <p><img src="../uploads/'.$row['msg_img'].'" alt="Image"></p>
                                    </div>
                                </div>';
                } else {
                    $output .= '<div class="chat outgoing">
                                    <div class="details">
                                        <p>'.$row['msg'].'</p>
                                    </div>
                                </div>';
                }
            }
            // Incoming messages (from the other user)
            else {
                if(empty($row['msg']) && !empty($row['msg_img'])){ // If the message is an image
                    $output .= '<div class="chat incoming">
                                    <img src="../uploads/'.$row2['profile_photo'].'" alt="User Photo">
                                    <div class="details">
                                        <p><img src="../uploads/'.$row['msg_img'].'" alt="Image"></p>
                                    </div>
                                </div>';
                } else {
                    $output .= '<div class="chat incoming">
                                    <img src="../uploads/'.$row2['profile_photo'].'" alt="User Photo">
                                    <div class="details">
                                        <p>'.$row['msg'].'</p>
                                    </div>
                                </div>';
                }
            }
        }
    } else {
        $output .= '<div class="text">
                        <img src="../uploads/'.$row2['profile_photo'].'" alt="User Photo">
                        <span>No messages are available. Once you send a message, it will appear here.</span>
                    </div>';
    }

    echo $output;
} else {
    header('Location: login-signup.php');
    exit();
}
?>
