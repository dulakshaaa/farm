<?php
require_once '../includes/connect.php';
include '../includes/breednavbar.php';

// Initialize variables
$message = '';
$brdsno = isset($_GET['id']) ? $_GET['id'] : '';

// Fetch breed data if ID is provided
if ($brdsno) {
    $stmt = $conn->prepare("SELECT 
                            b.BRDSNO, 
                            b.BRDNAME,
                            b.BRDCODE,
                            b.BRDACTFLG
                          FROM BREEDMAST b
                          WHERE b.BRDSNO = ?");
    $stmt->bind_param("i", $brdsno);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
}


// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $brdsno = $_POST['brdsno'];
    $brdname = trim($_POST['brdname']);
    $brdcode = trim($_POST['brdcode']);
    $brdstatus = $_POST['brdstatus']; // Get the selected status value
    $user = $_SESSION['username'] ?? 'unknown_user';
    $user_ip = $_SERVER['REMOTE_ADDR'];

    // Get the current date and time
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');
    
    // Validate inputs
    if (empty($brdname) || empty($brdcode)) {
        $message = "<div class='error'>Breed name and code are required!</div>";
    } else {
        $stmt = $conn->prepare("UPDATE BREEDMAST SET 
                              BRDNAME = ?, 
                              BRDCODE = ?, 
                              BRDACTFLG = ?,
                              BRDAMDDUSER = ?, 
                              BRDAMDDIP =?, 
                              BRDAMDDT = ?, 
                              BRDAMDTIME = ? 
                              WHERE BRDSNO = ?");

        $stmt->bind_param("ssissssi", $brdname, $brdcode, $brdstatus, $user, $user_ip, $current_date, $current_time, $brdsno);

        if ($stmt->execute()) {
            $message = "<div class='success'>Breed record updated successfully!</div>";
            
            // Refresh the data after update
            $stmt = $conn->prepare("SELECT 
                                    b.BRDSNO, 
                                    b.BRDNAME,
                                    b.BRDCODE,
                                    b.BRDACTFLG
                                    
                                  FROM BREEDMAST b
                                  WHERE b.BRDSNO = ?");
            $stmt->bind_param("i", $brdsno);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
        } else {
            $message = "<div class='error'>Error updating breed record: " . $stmt->error . "</div>";
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
    <title>Amend Breed Record</title>
</head>

<body>
    <h1>Amend Breed Record</h1>

    <?php echo $message; ?>

    <div class="container">
        <form method="post">
            <input type="hidden" name="brdsno" value="<?php echo htmlspecialchars($row['BRDSNO'] ?? ''); ?>">

            <div class="form-group">
                <label for="brdsno">Breed S.No:</label>
                <input type="text" id="brdsno" value="<?php echo htmlspecialchars($row['BRDSNO'] ?? ''); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="brdname">Breed Name:</label>
                <input type="text" id="brdname" name="brdname" value="<?php echo htmlspecialchars($row['BRDNAME'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="brdcode">Breed Code:</label>
                <input type="text" id="brdcode" name="brdcode" value="<?php echo htmlspecialchars($row['BRDCODE'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="brdstatus">Active Status:</label>
                <select id="brdstatus" name="brdstatus" required>
                    <option value="1" <?php echo ($row['BRDACTFLG'] == 1) ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo ($row['BRDACTFLG'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" name="update" class="btn-update">Update Breed</button>
                <a href="../view/breeds.php" class="button">Cancel</a>
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
