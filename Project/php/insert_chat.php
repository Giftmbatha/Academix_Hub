<?php
session_start();
if(isset($_SESSION['user_id'])){
    include "../includes/database.php";
    
    $outgoing_id = $_SESSION['user_id'];
    $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // Insert text message
    if(!empty($message)){
        $insert_msg = mysqli_query($conn, "INSERT INTO messages (outgoing_msg_id, incoming_msg_id, msg) 
                                            VALUES ('$outgoing_id', '$incoming_id', '$message')");
    }
    
    // Check if an image is being sent
    if(isset($_FILES['send_image'])){
        $send_image = $_FILES['send_image']['name']; // User image name
        $send_image_size = $_FILES['send_image']['size']; // User image size
        $send_image_tmp_name = $_FILES['send_image']['tmp_name'];
        
        // Check file type (for example, allow only jpg, png, jpeg)
        $allowed_types = array('jpg', 'jpeg', 'png');
        $file_extension = pathinfo($send_image, PATHINFO_EXTENSION);
        
        if(in_array($file_extension, $allowed_types)){
            // Check if the image size is not too large (max size example: 5MB)
            if($send_image_size < 5000000){
                // Rename the image to prevent conflicts
                $image_rename = time() . "_" . preg_replace('/\s+/', '_', $send_image); 
                $image_folder = '../uploads/' . $image_rename;
                
                // Move the uploaded file to the destination folder
                if(move_uploaded_file($send_image_tmp_name, $image_folder)){
                    // Insert image message into the database
                    $insert_msg_img = mysqli_query($conn, "INSERT INTO messages (outgoing_msg_id, incoming_msg_id, msg_img) 
                                                           VALUES ('$outgoing_id', '$incoming_id', '$image_rename')");
                } else {
                    echo "Failed to upload image.";
                }
            } else {
                echo "Image size is too large. Maximum allowed size is 5MB.";
            }
        } else {
            echo "Invalid file type. Only JPG, JPEG, and PNG files are allowed.";
        }
    }

} else {
    header('Location: login-signup.php');
    exit();
}
?>
