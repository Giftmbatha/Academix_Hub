<?php 
include '../includes/database.php'; // including the database connection
session_start();
$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
    header('location: login-signup.php');
}

// Get the recipient user ID from the query string
if (isset($_GET['user_id'])) {
    $recipient_id = mysqli_real_escape_string($conn, $_GET['user_id']);
} else {
    header('location: chats.php'); // Redirect if no user is specified
}

// Fetch the current user's details
$select = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$current_user = mysqli_fetch_assoc($select);

// Fetch the recipient's details
$recipient_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$recipient_id'");
$recipient = mysqli_fetch_assoc($recipient_query);

if (!$recipient) {
    header('location: chats.php'); // Redirect if recipient doesn't exist
}

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Tracking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
    <div class="container">
        <section class="chat-area">
            <header>
                <a href="base_dashboard.php" class="back-icon"><img src="../images/arrow.svg" alt="Back"></a>
                <img src="uploads/<?php echo htmlspecialchars($row['profile_photo']); ?>" alt="User Photo">
                <div class="details">
                    <span><?php echo htmlspecialchars($row['username']); ?></span>
                    <p>Online</p>
                </div>
            </header>
            <div class="chat-box">
                <!-- Messages will be dynamically loaded here -->
            </div>
            <form action="" class="typing-area" method="POST" enctype="multipart/form-data">
                <input type="text" name="incoming_id" value="<?php echo $user_id ?>" class="incoming_id" hidden>
                <input type="text" name="message" class="input-field" placeholder="Type a message here....">
                <button type="button" class="image"><img src="images/camera.svg" alt="Image"></button>
                <input type="file" name="send_image" accept="image/*" class="upload_img" hidden>
                <button type="submit" class="send_btn" name="send_btn"><img src="images/send.svg" alt="Send"></button>
            </form>
        </section>
    </div>

    <script>
        const sendImageBtn = document.querySelector(".typing-area .image");
        const imageInput = document.querySelector(".typing-area .upload_img");
        const form = document.querySelector(".typing-area"),
              incoming_id = form.querySelector(".incoming_id").value,
              sendBtn = form.querySelector(".send_btn"),
              inputField = form.querySelector(".input-field"),
              chatBox = document.querySelector(".chat-box");

        sendImageBtn.onclick = () => {
            imageInput.click();
        };

        form.onsubmit = (e) => {
            e.preventDefault();
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "/php/insert_chat.php", true);
            xhr.onload = () => {
                if(xhr.readyState === XMLHttpRequest.DONE){
                    if(xhr.status === 200){
                        inputField.value = "";
                        imageInput.value = "";
                        scrollBottom();
                        sendBtn.classList.remove("active");
                    }
                }
            };
            let formData = new FormData(form);
            xhr.send(formData);
        };

        inputField.onkeyup = () => {
            if(inputField.value.trim() != ""){
                sendBtn.classList.add("active");
            } else {
                sendBtn.classList.remove("active");
            }
        };

        imageInput.oninput = () => {
            if(imageInput.value != ""){
                sendBtn.classList.add("active");
            } else {
                sendBtn.classList.remove("active");
            }
        };

        chatBox.onmouseenter = () => {
            chatBox.classList.add("active");
        };

        chatBox.onmouseleave = () => {
            chatBox.classList.remove("active");
        };

        setInterval(() => {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "/php/get_chat.php", true);
            xhr.onload = () => {
                if(xhr.readyState === XMLHttpRequest.DONE){
                    if(xhr.status === 200){
                        let data = xhr.response;
                        chatBox.innerHTML = data;
                        if(!chatBox.classList.contains("active")){
                            scrollBottom();
                        }
                    }
                }
            };
            xhr.setRequestHeader("content-type", "application/x-www-form-urlencoded");
            xhr.send("incoming_id=" + incoming_id);
        }, 500);

        function scrollBottom(){
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    </script>


