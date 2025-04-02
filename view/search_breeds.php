<?php
require_once '../includes/connect.php';

header('Content-Type: application/json');

if (isset($_GET['term'])) {
    $term = $conn->real_escape_string($_GET['term']);
    
    $sql = "SELECT DISTINCT BRDCODE FROM BREEDMAST WHERE BRDCODE LIKE '%$term%' LIMIT 10";
    $result = $conn->query($sql);
    
    $breeds = array();
    while ($row = $result->fetch_assoc()) {
        $breeds[] = $row['BRDCODE'];
    }
    
    echo json_encode($breeds);
}

$conn->close();
?>