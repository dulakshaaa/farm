<?php
include '../includes/connect.php';  // Include database connection
require_login();  // This will redirect to login if not authenticated
require_role('admin');
//require_once '../includes/geolocation_access.php';

// Get current user data
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM usemast WHERE USRSNO = $user_id");
$current_user = $user_query->fetch_assoc();


// Fetch existing system records for reference
$systems = [];
$sysQuery = "SELECT syssno, sysdes1 FROM sysmast ORDER BY sysdes1";
$sysResult = $conn->query($sysQuery);
if ($sysResult && $sysResult->num_rows > 0) {
    while ($row = $sysResult->fetch_assoc()) {
        $systems[] = $row;
    }
}



//--------------------------------------------Add/Update System Data-------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $syssno = $_POST['syssno'] ?? '';
    $sysrno = $_POST['sysrno'];
    $sysrtp = $_POST['sysrtp'];
    $sysdes1 = $_POST['sysdes1'];
    $sysdes2 = $_POST['sysdes2'];
    $adduser = $_SESSION['username'] ?? 'unknown_user';
    $addip = gethostbyname(gethostname());

    if (empty($_POST['syssno'])) {
        // Insert new record - syssno is auto-increment
        $sql = "INSERT INTO sysmast (sysrno, sysrtp, sysdes1, sysdes2, sysadip, sysadusr, sysaddt, sysadtime) 
                VALUES (?, ?, ?, ?, ?, ?, CURRENT_DATE, CURRENT_TIME)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $sysrno, $sysrtp, $sysdes1, $sysdes2, $addip, $adduser);

        if ($stmt->execute()) {
            echo "<script>alert('New System added successfully');</script>";
        }
    } else {
        echo "<script>alert('Error: " . addslashes($conn->error) . "');</script>";
    }
     header("Location: ../amend/sysamend.php?id=" . urlencode($syssno));
    exit;
}


$conn->close();
?>
<?php include '../includes/sysaddnav.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/add.css">
    <title>System Master</title>
    <style>
        .form-container {
            max-width: 800px;
            margin: 20px auto;
        }

        .system-list {
            margin-top: 30px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
        }

        .system-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .system-list th,
        .system-list td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .system-list th {
            background-color: #f2f2f2;
        }

        .action-links a {
            margin-right: 10px;
            color: #3498db;
            text-decoration: none;
        }

        .action-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <form method="POST" action="">
            <h1>Add System Record</h1>

            <input type="hidden" name="syssno" value="<?php echo $edit_record['syssno'] ?? ''; ?>">

            <div class="form-group">
                <label for="sysrno">System Reference Number</label>
                <input type="number" id="sysrno" name="sysrno" required
                    value="<?php echo $edit_record['sysrno'] ?? ''; ?>">
            </div>

            <div class="form-group">
                <label for="sysrtp">System Type</label>
                <input type="text" id="sysrtp" name="sysrtp" required
                    value="<?php echo $edit_record['sysrtp'] ?? ''; ?>">
            </div>

            <div class="form-group">
                <label for="sysdes1">Description 1</label>
                <input type="text" id="sysdes1" name="sysdes1" required
                    value="<?php echo $edit_record['sysdes1'] ?? ''; ?>">
            </div>

            <div class="form-group">
                <label for="sysdes2">Description 2</label>
                <input type="text" id="sysdes2" name="sysdes2"
                    value="<?php echo $edit_record['sysdes2'] ?? ''; ?>">
            </div>

            <div class="form-actions">
                <button type="reset">Reset</button>
                <button type="submit"><?php echo isset($edit_record) ? 'Update' : 'Add'; ?> Record</button>
                <?php if (isset($edit_record)): ?>
                    <a href="sysmast.php" class="cancel-btn">Cancel</a>
                <?php endif; ?>
            </div>
        </form>


    </div>
</body>

</html>