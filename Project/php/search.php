<?php 
include '../includes/database.php'; // Including the database connection
session_start();

$searchOn = isset($_POST['searchOn']) ? $_POST['searchOn'] : ''; // Check if 'searchOn' exists in the POST request
$output = ""; // Initialize output variable

if (!empty($searchOn)) {
    $searchOn = mysqli_real_escape_string($conn, $searchOn); // Escape the string safely
    
    // Query to search users based on the input
    $query = "SELECT * FROM users WHERE username LIKE '%$searchOn%' OR email LIKE '%$searchOn%'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $output .= '<a href="chat.php?user_id='.$row['id'].'">
                            <div class="content">
                                <img src="uploads/'.$row['profile_photo'].'" alt="">
                                <div class="details">
                                    <span>'.$row['username'].'</span>
                                    <p>Online</p> <!-- Placeholder text -->
                                </div>
                            </div>
                            <div class="status-dot"></div>
                        </a>';
        }
    } else {
        $output .= '<div class="text">No users found</div>';
    }
} else {
    $output .= '<div class="text">Please enter a name or email to search.</div>';
}

echo $output; // Output the result
?>

