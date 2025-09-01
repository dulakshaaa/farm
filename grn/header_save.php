<?php
include 'connect.php';


$name = $_POST['name'];
$age = $_POST['age'];

$sql = "INSERT INTO inhtran (names, age) VALUES ('$name', $age)";
if ($conn->query($sql)) {
    // return inserted ID to link with form 2
    echo json_encode(['status' => 'success', 'userid' => $conn->insert_id]);
} else {
    echo json_encode(['status' => 'error']);
}





?>






