<?php
include '../includes/connect.php';
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$search = isset($_GET['q']) ? trim($_GET['q']) : "";

if ($search === "") {
    $sql = "SELECT stksno, stkdesc, stkcode FROM stkmast LIMIT 50";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT stksno, stkdesc, stkcode FROM stkmast WHERE stkdesc LIKE ? OR stkcode LIKE ? LIMIT 10";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["error" => $conn->error]);
        exit;
    }
    $like = "%" . $search . "%";
    $stmt->bind_param("ss", $like, $like);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id" => $row['stksno'],
        "name" => $row['stkdesc'],
        "code" => $row["stkcode"]
    ];
}

echo json_encode($data);

$stmt->close();
$conn->close();
?>
