<?php
require_once '../includes/connect.php';

header('Content-Type: application/json');

if (isset($_GET['term'])) {
    $term = $conn->real_escape_string($_GET['term']);
    
    // Query to search farm name and code
    $sql = "SELECT DISTINCT FARNAME, FARCODE FROM FARMA WHERE FARNAME LIKE '%$term%' OR FARCODE LIKE '%$term%' LIMIT 10";
    $result = $conn->query($sql);
    
    $farms = array();
    while ($row = $result->fetch_assoc()) {
        // Combine farm name and code for suggestions
        $farms[] = array(
            'label' => $row['FARNAME'] . ' (' . $row['FARCODE'] . ')', // Display farm name with code
            'value' => $row['FARNAME'] // The value used after selection, you can change to FARCODE if preferred
        );
    }
    
    echo json_encode($farms);
}

$conn->close();
?>
