<?php
require '../includes/connect.php';

$sql = "SELECT syssno, sysdes1 FROM sysmast WHERE sysrno = 7";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "syssno" => $row['syssno'],
        "sysdes1" => $row['sysdes1']
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No unit found"
    ]);
}
