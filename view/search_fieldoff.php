<?php
require_once '../includes/connect.php';

header('Content-Type: application/json');

if (isset($_GET['term'])) {
    $term = $conn->real_escape_string($_GET['term']);
    
    // Search by Field Officer Name (FLONAME) with case-insensitive matching
    $sql = "SELECT DISTINCT FLONAME FROM FLOMAST 
            WHERE FLONAME LIKE '%$term%' 
            ORDER BY FLONAME 
            LIMIT 10";
    $result = $conn->query($sql);
    
    $officers = array();
    while ($row = $result->fetch_assoc()) {
        $officers[] = $row['FLONAME'];
    }
    
    echo json_encode($officers);
}

$conn->close();
?>