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

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Authentication Helpers ---

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: /farm/farm/includes/login.php");
        exit();
    }
}

function require_role($role) {
    if (!is_logged_in() || !isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: /farm/farm/includes/403.php"); // Optional: custom forbidden page
        exit();
    }
}

// --- Get Current Logged-in User ---
$current_user = null;
if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM usemast WHERE USRSNO = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_user = $result->fetch_assoc();
    $stmt->close();
}
?>
