<?php
require_once '../includes/connect.php';

header('Content-Type: application/json');

if (isset($_GET['term'])) {
    $term = $conn->real_escape_string($_GET['term']);
    
    $sql = "SELECT DISTINCT BATCODE FROM BATMAST WHERE BATCODE LIKE '%$term%' LIMIT 10";
    $result = $conn->query($sql);
    
    $batches = array();
    while ($row = $result->fetch_assoc()) {
        $batches[] = $row['BATCODE'];
    }
    
    echo json_encode($batches);
}

$conn->close();
?>