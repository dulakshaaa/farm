<?php
include './connect2.php';
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$search = isset($_GET['q']) ? trim($_GET['q']) : "";

if ($search === "") {
    // Show first 50 locations
    $sql = "SELECT inhsno, inhdno FROM inhtran LIMIT 50";
    $stmt = $conn->prepare($sql);
} else {
    // Search locations by name
    $sql = "SELECT inhsno, inhdno FROM inhtran WHERE inhdno LIKE ? LIMIT 10";
    $stmt = $conn->prepare($sql);
    $like = "%" . $search . "%";
    $stmt->bind_param("s", $like);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id" => $row['inhsno'],   // return as "id" for frontend
        "name" => $row['inhdno'],  // return as "name"
        "code" => ""
    ];
}

echo json_encode($data);

$stmt->close();
$conn->close();
?>
