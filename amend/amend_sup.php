<?php
require_once '../includes/connect.php';
require_login();
include '../includes/areanav.php';

$supsno = $_GET['id'] ?? 0;
$message = '';
$supplier = [];

if ($supsno) {
    $stmt = $conn->prepare("SELECT * FROM supmast WHERE supsno = ?");
    $stmt->bind_param("i", $supsno);
    $stmt->execute();
    $result = $stmt->get_result();
    $supplier = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $supsno = $_POST['supsno'];
    $supcode = $_POST['supcode'];
    $supnam = $_POST['supnam'];
    $supdes1 = $_POST['supdes1'];
    $supdes2 = $_POST['supdes2'];
    $user = $_SESSION['username'] ?? 'unknown_user';
    $user_ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $conn->prepare("UPDATE supmast SET 
                          supcode = ?, 
                          supnam = ?, 
                          supdes1 = ?,
                          supdes2 = ?,
                          supamusr = ?,
                          supamdip = ?
                          WHERE supsno = ?");
    $stmt->bind_param("ssssssi", $supcode, $supnam, $supdes1, $supdes2, $user, $user_ip, $supsno);

    if ($stmt->execute()) {
        $message = "<div class='success'>Supplier updated successfully!</div>";
        // Refresh data
        $stmt = $conn->prepare("SELECT * FROM supmast WHERE supsno = ?");
        $stmt->bind_param("i", $supsno);
        $stmt->execute();
        $result = $stmt->get_result();
        $supplier = $result->fetch_assoc();
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
    <title>Amend Supplier</title>
</head>
<body>
    <h1>Amend Supplier</h1>
    <?= $message ?>

    <div class="container">
        <form method="post">
            <input type="hidden" name="supsno" value="<?= htmlspecialchars($supplier['supsno'] ?? '') ?>">

            <div class="form-group">
                <label>Supplier Code:</label>
                <input type="text" name="supcode" value="<?= htmlspecialchars($supplier['supcode'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Supplier Name:</label>
                <input type="text" name="supnam" value="<?= htmlspecialchars($supplier['supnam'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Description 1:</label>
                <input type="text" name="supdes1" value="<?= htmlspecialchars($supplier['supdes1'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Description 2:</label>
                <input type="text" name="supdes2" value="<?= htmlspecialchars($supplier['supdes2'] ?? '') ?>">
            </div>

            <div class="form-actions">
                <button type="submit" name="update">Update</button>
                <a href="view_sup.php" class="button">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>