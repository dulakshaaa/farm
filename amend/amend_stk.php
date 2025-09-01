<?php
require_once '../includes/connect.php';
require_login();
include '../includes/areanav.php';

$stksno = $_GET['id'] ?? 0;
$message = '';
$stock = [];

if ($stksno) {
    $stmt = $conn->prepare("SELECT * FROM stkmast WHERE stksno = ?");
    $stmt->bind_param("i", $stksno);
    $stmt->execute();
    $result = $stmt->get_result();
    $stock = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $stksno = $_POST['stksno'];
    $stkcode = $_POST['stkcode'];
    $stkdesc = $_POST['stkdesc'];
    $stkdes1 = $_POST['stkdes1'];
    $stkdes2 = $_POST['stkdes2'];
    $user = $_SESSION['username'] ?? 'unknown_user';
    $user_ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $conn->prepare("UPDATE stkmast SET 
                          stkcode = ?, 
                          stkdesc = ?, 
                          stkdes1 = ?,
                          stkdes2 = ?,
                          stkamusr = ?,
                          stkamdip = ?
                          WHERE stksno = ?");
    $stmt->bind_param("ssssssi", $stkcode, $stkdesc, $stkdes1, $stkdes2, $user, $user_ip, $stksno);

    if ($stmt->execute()) {
        $message = "<div class='success'>Stock updated successfully!</div>";
        // Refresh data
        $stmt = $conn->prepare("SELECT * FROM stkmast WHERE stksno = ?");
        $stmt->bind_param("i", $stksno);
        $stmt->execute();
        $result = $stmt->get_result();
        $stock = $result->fetch_assoc();
    } else {
        $message = "<div class='error'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/amend.css">
    <title>Amend Stock</title>
</head>
<body>
    <h1>Amend Stock</h1>
    <?= $message ?>

    <div class="container">
        <form method="post">
            <input type="hidden" name="stksno" value="<?= htmlspecialchars($stock['stksno'] ?? '') ?>">

            <div class="form-group">
                <label>Stock Code:</label>
                <input type="text" name="stkcode" value="<?= htmlspecialchars($stock['stkcode'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Description:</label>
                <input type="text" name="stkdesc" value="<?= htmlspecialchars($stock['stkdesc'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Description 1:</label>
                <input type="text" name="stkdes1" value="<?= htmlspecialchars($stock['stkdes1'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Description 2:</label>
                <input type="text" name="stkdes2" value="<?= htmlspecialchars($stock['stkdes2'] ?? '') ?>">
            </div>

            <div class="form-actions">
                <button type="submit" name="update">Update</button>
                <a href="view_stk.php" class="button">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>