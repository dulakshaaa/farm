<?php
require_once '../includes/connect.php';

// Check if batcode is set in the URL
if (isset($_GET['batcode']) && !empty($_GET['batcode'])) {
    $batcode = $conn->real_escape_string($_GET['batcode']);

    // Delete query
    $sql = "DELETE FROM BATMAST WHERE BATCODE = '$batcode'";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Record deleted successfully'); window.location.href='../view/batches.php';</script>";
    } else {
        echo "<script>alert('Error deleting record: " . $conn->error . "'); window.location.href='../view/batches.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request'); window.location.href='../view/batches.php';</script>";
}

$conn->close();
?>
