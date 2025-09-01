<?php
require_once '../includes/connect.php';
require_login();
include '../includes/areanav.php';

$locsno = $_GET['id'] ?? 0;
$message = '';
$location = [];
$companies = [];

// Fetch companies
$companyQuery = "SELECT syssno, sysdes1 FROM sysmast ORDER BY sysdes1";
$companyResult = $conn->query($companyQuery);
if ($companyResult && $companyResult->num_rows > 0) {
    while ($row = $companyResult->fetch_assoc()) {
        $companies[] = $row;
    }
}

if ($locsno) {
    $stmt = $conn->prepare("SELECT * FROM locmast WHERE locsno = ?");
    $stmt->bind_param("i", $locsno);
    $stmt->execute();
    $result = $stmt->get_result();
    $location = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $locsno = $_POST['locsno'];
    $loccomsno = $_POST['loccomsno'];
    $locnam = $_POST['locnam'];
    $locdes1 = $_POST['locdes1'];
    $locdes2 = $_POST['locdes2'];
    $user = $_SESSION['username'] ?? 'unknown_user';
    $user_ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $conn->prepare("UPDATE locmast SET 
                          loccomsno = ?, 
                          locnam = ?, 
                          locdes1 = ?,
                          locdes2 = ?,
                          locamusr = ?,
                          locamdip = ?
                          WHERE locsno = ?");
    $stmt->bind_param("isssssi", $loccomsno, $locnam, $locdes1, $locdes2, $user, $user_ip, $locsno);

    if ($stmt->execute()) {
        $message = "<div class='success'>Location updated successfully!</div>";
        // Refresh data
        $stmt = $conn->prepare("SELECT * FROM locmast WHERE locsno = ?");
        $stmt->bind_param("i", $locsno);
        $stmt->execute();
        $result = $stmt->get_result();
        $location = $result->fetch_assoc();
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
    <title>Amend Location</title>
</head>
<body>
    <h1>Amend Location</h1>
    <?= $message ?>

    <div class="container">
        <form method="post">
            <input type="hidden" name="locsno" value="<?= htmlspecialchars($location['locsno'] ?? '') ?>">

            <div class="form-group">
                <label>Company:</label>
                <select name="loccomsno" required>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?= $company['syssno'] ?>" 
                            <?= ($location['loccomsno'] ?? '') == $company['syssno'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($company['sysdes1']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Location Name:</label>
                <input type="text" name="locnam" value="<?= htmlspecialchars($location['locnam'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Description 1:</label>
                <input type="text" name="locdes1" value="<?= htmlspecialchars($location['locdes1'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Description 2:</label>
                <input type="text" name="locdes2" value="<?= htmlspecialchars($location['locdes2'] ?? '') ?>">
            </div>

            <div class="form-actions">
                <button type="submit" name="update">Update</button>
                <a href="view_loc.php" class="button">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>