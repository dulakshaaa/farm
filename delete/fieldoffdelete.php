<?php
require_once '../includes/connect.php';

// Check if field officer ID is set in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $officerId = $conn->real_escape_string($_GET['id']);

    // Delete query for FLOMAST table
    $sql = "DELETE FROM FLOMAST WHERE FLOSNO = '$officerId'";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Field officer deleted successfully'); 
                window.location.href='../view/fieldoff.php';
              </script>";
    } else {
        echo "<script>
                alert('Error deleting field officer: " . addslashes($conn->error) . "'); 
                window.location.href='../view/fieldoff.php';
              </script>";
    }
} else {
    echo "<script>
            alert('Invalid request'); 
            window.location.href='../view/fieldoff.php';
          </script>";
}

$conn->close();
?>