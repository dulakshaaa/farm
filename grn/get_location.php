<?php

require '../includes/connect.php';



if (isset($_SESSION['location'])) {
    $locId = intval($_SESSION['location']);
    $sql = "SELECT locsno, locnam FROM locmast WHERE locsno = $locId";
    $result = $conn->query($sql);

  

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(value: [
            "status" => "success",
            "locsno" => $row['locsno'],
            "locname" => $row['locnam']
        ]);
    }
        else {
        echo json_encode([
            "status" => "error",
            "message" => "No loc found"
        ]);
    }
}



