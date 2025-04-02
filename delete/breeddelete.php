<?php
require_once '../includes/connect.php';

// Check if 'id' parameter is passed
if (isset($_GET['id']) && !empty($_GET['id'])) {
    // Sanitize the input value
    $brdsno = $conn->real_escape_string($_GET['id']);
    
    // Delete query
    $sql = "DELETE FROM BREEDMAST WHERE BRDSNO = '$brdsno'";

    // Execute the query and check if successful
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Record deleted successfully'); window.location.href='../view/breeds.php';</script>";
    } else {
        echo "<script>alert('Error deleting record: " . $conn->error . "'); window.location.href='../view/breeds.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request'); window.location.href='../view/breeds.php';</script>";
}

// Close the database connection
$conn->close();
?>
