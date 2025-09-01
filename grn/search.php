<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include './includes/connect.php';

$search = isset($_GET['q']) ? $_GET['q'] : "";

if ($search === "") {
    $sql = "SELECT id, name FROM locmast LIMIT 50"; // adjust limit if large
    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT id, name FROM fruits WHERE name LIKE ? LIMIT 10";
    $stmt = $conn->prepare($sql);
    $like = "%" . $search . "%";
    $stmt->bind_param("s", $like);
}
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id" => $row['id'],
        "name" => $row['name']
    ];
}

echo json_encode($data);

$stmt->close();
$conn->close();
?>
