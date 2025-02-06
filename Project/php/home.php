<?php 
    session_start();
    include '../includes/database.php';

    // Check if user is logged in
    if(!isset($_SESSION['user_id'])){
        echo "You are not logged in!";
        exit();
    }

    $outgoing_id = $_SESSION['user_id'];
    $searchOn = mysqli_real_escape_string($conn, $_POST['searchOn']);

    // Fetch users excluding the current user and matching search criteria
    $sql = "SELECT * FROM users WHERE id != {$outgoing_id}
            AND (username LIKE '%{$searchOn}%' OR email LIKE '%{$searchOn}%')";
    $query = mysqli_query($conn, $sql);
    $output = "";

    if(mysqli_num_rows($query) == 0){
        $output = "No users are available to chat";
    } else {
        while ($row = mysqli_fetch_assoc($query)) {
            // Assuming user_data.php formats the user data properly
            // Otherwise, you can build the output directly here
            $output .= '<div class="user-box">';
            $output .= '<img src="uploads/'.$row['profile_photo'].'" alt="User Photo">';
            $output .= '<div class="user-details">';
            $output .= '<span>'.$row['username'].'</span>';
            $output .= '<p>'.$row['email'].'</p>';
            $output .= '</div>';
            $output .= '</div>';
        }
    }

    echo $output;
?>
