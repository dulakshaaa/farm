<?php
require_once '../includes/connect.php';
require_login();

$id = $_GET['id'] ?? 0;

// Check if system exists
$check = $conn->prepare("SELECT syssno FROM sysmast WHERE syssno = ?");
$check->bind_param("i", $id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $delete = $conn->prepare("DELETE FROM sysmast WHERE syssno = ?");
    $delete->bind_param("i", $id);
    
    if ($delete->execute()) {
        header("Location: ../view/sys.php?deleted=1");
    } else {
        header("Location: ../view/sys.php?error=delete_failed");
    }
} else {
    header("Location: ../view/sys.php?error=not_found");
}

$conn->close();
?>