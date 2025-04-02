<?php
require_once '../includes/connect.php';

// Check if area ID is set in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $areaId = $conn->real_escape_string($_GET['id']);

    // Delete query for AREAMAST table
    $sql = "DELETE FROM AREAMAST WHERE AREASNO = '$areaId'";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Area deleted successfully'); 
                window.location.href='../view/area.php';
              </script>";
    } else {
        echo "<script>
                alert('Error deleting area: " . addslashes($conn->error) . "'); 
                window.location.href='../view/area.php';
              </script>";
    }
} else {
    echo "<script>
            alert('Invalid request'); 
            window.location.href='../view/area.php';
          </script>";
}

$conn->close();
?>