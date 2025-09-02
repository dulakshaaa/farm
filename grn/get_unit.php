<?php
require '../includes/connect.php';

$sql = "SELECT * FROM sysmast WHERE sysrno = 7";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "syssno"  => $row['syssno'],
            "sysdes1" => $row['sysdes1']
        ];
    }

    echo json_encode([
        "status" => "success",
        "units"  => $data
    ]);
} else {
    echo json_encode([
        "status"  => "error",
        "message" => "No units found"
    ]);
}
