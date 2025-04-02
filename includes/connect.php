<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farms";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8mb4");

// Start session
session_start();

// Authentication functions
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: login.php");
        exit();
    }
}

// Get current user data if logged in
$current_user = null;
if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    $result = $conn->query("SELECT * FROM usemast WHERE USRSNO = $user_id");
    $current_user = $result->fetch_assoc();
}
?>