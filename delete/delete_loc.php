<?php
require_once '../includes/connect.php';
require_login();

if (isset($_GET['id'])) {
    $locsno = $conn->real_escape_string($_GET['id']);
    $sql = "DELETE FROM locmast WHERE locsno = '$locsno'";
    
    if ($conn->query($sql)) {
        echo "<script>
                alert('Location deleted successfully'); 
                window.location.href='view_loc.php';
              </script>";
    } else {
        echo "<script>
                alert('Error deleting location'); 
                window.location.href='view_loc.php';
              </script>";
    }
} else {
    header("Location: ../view/loc_view.php");
}

$conn->close();
?>