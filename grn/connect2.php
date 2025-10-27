<?php
// Database credentials
$servername = "localhost";  // usually localhost
$username = "root";         // your DB username
$password = "";             // your DB password
$database = "farms";    // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Optional: set charset
$conn->set_charset("utf8");

?>
