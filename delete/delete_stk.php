<?php
require_once '../includes/connect.php';
require_login();

if (isset($_GET['id'])) {
    $stksno = $conn->real_escape_string($_GET['id']);
    $sql = "DELETE FROM stkmast WHERE stksno = '$stksno'";
    
    if ($conn->query($sql)) {
        echo "<script>
                alert('Stock deleted successfully'); 
                window.location.href='view_stk.php';
              </script>";
    } else {
        echo "<script>
                alert('Error deleting stock'); 
                window.location.href='view_stk.php';
              </script>";
    }
} else {
    header("Location: view_stk.php");
}

$conn->close();
?>