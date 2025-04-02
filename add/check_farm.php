<?php
include '../includes/connect.php';

if (isset($_POST['batchCodePrefix'])) {
    $batchCodePrefix = $_POST['batchCodePrefix'];

    // Query to find farms based on the batch code prefix
    $query = $conn->prepare("SELECT FARSNO, FARNAME FROM farma WHERE FARCODE LIKE ?");
    $searchTerm = "%$batchCodePrefix%";  // Search for farms that contain the batch code prefix
    $query->bind_param("s", $searchTerm);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $farms = [];
        while ($farm = $result->fetch_assoc()) {
            $farms[] = $farm;
        }
        echo json_encode(['success' => true, 'farms' => $farms]);
    } else {
        echo json_encode(['success' => false, 'farms' => []]);
    }

    $query->close();
} else {
    echo json_encode(['success' => false, 'farms' => []]);
}
