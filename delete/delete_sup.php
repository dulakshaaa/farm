<?php
require_once '../includes/connect.php';
require_login();

if (isset($_GET['id'])) {
    $stksno = $conn->real_escape_string($_GET['id']);
    $sql = "DELETE FROM supmast WHERE supsno = '$supsno'";
    
    if ($conn->query($sql)) {
        echo "<script>
                alert('Stock deleted successfully'); 
                window.location.href='view_sup.php';
              </script>";
    } else {
        echo "<script>
                alert('Error deleting stock'); 
                window.location.href='view_sup.php';
              </script>";
    }
} else {
    header("Location: view_sup.php");
}

$conn->close();
?>