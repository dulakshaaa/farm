<?php
include '../includes/connect.php';
require_login();
require_role('admin');



$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM usemast WHERE USRSNO = $user_id");
$current_user = $user_query->fetch_assoc();
include '../includes/locnav.php';

// Fetch companies for dropdown
$companies = [];
$companyQuery = "SELECT syssno, sysdes1, sysrtp FROM sysmast WHERE sysrno = 200 ORDER BY sysdes1";
$companyResult = $conn->query($companyQuery);
if ($companyResult && $companyResult->num_rows > 0) {
    while ($row = $companyResult->fetch_assoc()) {
        $companies[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $loccomsno = $_POST['loccomsno'];
    $locnam    = trim($_POST['locnam']);
    $locdes1   = $_POST['locdes1'] ?? '';
    $locdes2   = $_POST['locdes2'] ?? '';
    $adduser   = $_SESSION['username'] ?? 'unknown_user';
    $addip     = gethostbyname(gethostname());

    try {
        // Check if location already exists
        $checkSql = "SELECT * FROM locmast WHERE loccomsno = ? AND locnam = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("is", $loccomsno, $locnam);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("Location already exists for this company.");
        }

        // Insert into locmast
        $sql = "INSERT INTO locmast (loccomsno, locnam, locdes1, locdes2, locadusr, locadip) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $loccomsno, $locnam, $locdes1, $locdes2, $adduser, $addip);
        if (!$stmt->execute()) {
            throw new Exception("Error inserting location: " . $stmt->error);
        }

        echo "<script>
                alert('New Location added successfully.');
                window.location.href = 'view_loc.php';
              </script>";

    } catch (Exception $e) {
        echo "<script>alert('" . addslashes($e->getMessage()) . "');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/add.css">
    <title>Add New Location</title>
</head>

<body>
    <div class="form-container">
        <form method="POST" action="">
            <h1>Add New Location</h1>

            <div class="form-group">
                <label for="loccomsno">Company</label>
                <select id="loccomsno" name="loccomsno" required>
                    <option value="">Select Company</option>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?= $company['syssno'] ?>"><?= htmlspecialchars($company['sysrtp']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="locnam">Location Name</label>
                <input type="text" id="locnam" name="locnam" required>
            </div>

            <div class="form-group">
                <label for="locdes1">Description 1</label>
                <input type="text" id="locdes1" name="locdes1">
            </div>

            <div class="form-group">
                <label for="locdes2">Description 2</label>
                <input type="text" id="locdes2" name="locdes2">
            </div>

            <div class="form-actions">
                <button type="reset">Reset</button>
                <button type="submit">Add Location</button>
            </div>
        </form>
    </div>
</body>

</html>

<?php $conn->close(); ?>
