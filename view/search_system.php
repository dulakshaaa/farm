<?php
require_once '../includes/connect.php';

$term = $_GET['term'] ?? '';

if (!empty($term)) {
    $term = $conn->real_escape_string($term);
    $query = "SELECT DISTINCT sysdes1 as value FROM sysmast WHERE sysdes1 LIKE '%$term%' 
              UNION 
              SELECT DISTINCT sysrtp as value FROM sysmast WHERE sysrtp LIKE '%$term%'
              UNION
              SELECT DISTINCT CONCAT( sysrno) as value FROM sysmast WHERE sysrno LIKE '%$term%'
              LIMIT 10";
    
    $result = $conn->query($query);
    $data = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    echo json_encode($data);
}

$conn->close();
?>