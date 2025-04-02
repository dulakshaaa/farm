<?php
require_once '../includes/connect.php';

// Get the search term from the request
$term = isset($_GET['term']) ? $conn->real_escape_string($_GET['term']) : '';

// Prepare and execute the query to search worker names
$sql = "SELECT WORKERNAME FROM workermast WHERE WORKERNAME LIKE '%$term%' LIMIT 10"; // Limit to 10 results
$result = $conn->query($sql);

// Prepare an array to store the results
$workers = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $workers[] = $row['WORKERNAME'];
    }
}

// Return the result as JSON
echo json_encode($workers);

// Close the database connection
$conn->close();
?>
