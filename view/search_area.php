<?php
require_once '../includes/connect.php';

// Get the search term from the request
$term = isset($_GET['term']) ? $conn->real_escape_string($_GET['term']) : '';

// Prepare and execute the query to search area names
$sql = "SELECT AREANAME FROM areamast WHERE AREANAME LIKE '%$term%' LIMIT 10"; // Limit to 10 results
$result = $conn->query($sql);

// Prepare an array to store the results
$areas = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $areas[] = $row['AREANAME'];
    }
}

// Return the result as JSON
echo json_encode($areas);

// Close the database connection
$conn->close();
?>
