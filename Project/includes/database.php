<?php
require_once 'config.php';

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "student_management_system";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}