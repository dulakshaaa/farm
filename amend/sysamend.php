<?php
require_once '../includes/connect.php';
require_login();  // Ensure user is authenticated
include '../includes/sysaddnav.php';

// Initialize variables
$message = '';
$syssno = isset($_GET['id']) ? $_GET['id'] : '';

// Fetch system data if ID is provided
if ($syssno) {
    $stmt = $conn->prepare("SELECT 
                            s.syssno, 
                            s.sysrno,
                            s.sysrtp,
                            s.sysdes1,
                            s.sysdes2
                          FROM sysmast s
                          WHERE s.syssno = ?");
    $stmt->bind_param("i", $syssno);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $syssno = $_POST['syssno'];
    $sysrno = trim($_POST['sysrno']);
    $sysrtp = trim($_POST['sysrtp']);
    $sysdes1 = trim($_POST['sysdes1']);
    $sysdes2 = trim($_POST['sysdes2']);
    $user = $_SESSION['username'] ?? 'unknown_user';
    $user_ip = $_SERVER['REMOTE_ADDR'];
    
    // Validate inputs
    if (empty($sysrno) || empty($sysrtp) || empty($sysdes1)) {
        $message = "<div class='error'>System reference number, type, and description 1 are required!</div>";
    } else {
        $stmt = $conn->prepare("UPDATE sysmast SET 
                              sysrno = ?, 
                              sysrtp = ?, 
                              sysdes1 = ?,
                              sysdes2 = ?,
                              sysamusr = ?,
                              sysamdip = ?,
                              sysamdt = CURRENT_DATE,
                              sysamtime = CURRENT_TIME
                              WHERE syssno = ?");

        $stmt->bind_param("isssssi", $sysrno, $sysrtp, $sysdes1, $sysdes2, $user, $user_ip, $syssno);

        if ($stmt->execute()) {
            $message = "<div class='success'>System record updated successfully!</div>";
            
            // Refresh the data after update
            $stmt = $conn->prepare("SELECT 
                                    s.syssno, 
                                    s.sysrno,
                                    s.sysrtp,
                                    s.sysdes1,
                                    s.sysdes2
                                  FROM sysmast s
                                  WHERE s.syssno = ?");
            $stmt->bind_param("i", $syssno);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
        } else {
            $message = "<div class='error'>Error updating system record: " . $stmt->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/amend.css">
    <title>Amend System Record</title>
    <style>
        /* Additional styles specific to system amend form */
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
    </style>
</head>

<body>
    <h1>Amend System Record</h1>

    <?php echo $message; ?>

    <div class="container">
        <form method="post">
            <input type="hidden" name="syssno" value="<?php echo htmlspecialchars($row['syssno'] ?? ''); ?>">

            <div class="form-group">
                <label for="syssno">System S.No:</label>
                <input type="text" id="syssno" value="<?php echo htmlspecialchars($row['syssno'] ?? ''); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="sysrno">Reference Number:</label>
                <input type="number" id="sysrno" name="sysrno" 
                       value="<?php echo htmlspecialchars($row['sysrno'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="sysrtp">System Type:</label>
                <input type="text" id="sysrtp" name="sysrtp" 
                       value="<?php echo htmlspecialchars($row['sysrtp'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="sysdes1">Description 1:</label>
                <input type="text" id="sysdes1" name="sysdes1" 
                       value="<?php echo htmlspecialchars($row['sysdes1'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="sysdes2">Description 2:</label>
                <input type="text" id="sysdes2" name="sysdes2" 
                       value="<?php echo htmlspecialchars($row['sysdes2'] ?? '');  ?>" required>
            </div>

            

            <div class="form-actions">
                <button type="submit" name="update" class="btn-update">Update System</button>
                <a href="../view/sys.php" class="button">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        window.onload = function() {
            const successMessage = document.querySelector('.success');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.classList.add('fade-out');
                }, 3000); // 3 seconds delay before fading out
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>