<?php
require_once '../includes/connect.php';
include '../includes/areanav.php'; // Include your area navigation bar

// Initialize variables
$message = '';
$areasno = isset($_GET['id']) ? $_GET['id'] : '';

// Fetch area data if ID is provided
if ($areasno) {
    $stmt = $conn->prepare("SELECT 
                            a.AREASNO, 
                            a.AREANAME,
                            a.AREACODE,
                            a.AREAACTFLG
                          FROM areamast a
                          WHERE a.AREASNO = ?");
    $stmt->bind_param("i", $areasno);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $areasno = $_POST['areasno'];
    $areaname = trim($_POST['areaname']);
    $areacode = trim($_POST['areacode']);
    $status = $_POST['status']; // Get the selected status value
    
    // Validate inputs
    if (empty($areaname) || empty($areacode)) {
        $message = "<div class='error'>Area name and code are required!</div>";
    } else {
        $stmt = $conn->prepare("UPDATE areamast SET 
                              AREANAME = ?, 
                              AREACODE = ?, 
                              AREAACTFLG = ?
                              WHERE AREASNO = ?");

        $stmt->bind_param("ssii", $areaname, $areacode, $status, $areasno);

        if ($stmt->execute()) {
            $message = "<div class='success'>Area record updated successfully!</div>";
            
            // Refresh the data after update
            $stmt = $conn->prepare("SELECT 
                                    a.AREASNO, 
                                    a.AREANAME,
                                    a.AREACODE,
                                    a.AREAACTFLG
                                  FROM areamast a
                                  WHERE a.AREASNO = ?");
            $stmt->bind_param("i", $areasno);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
        } else {
            $message = "<div class='error'>Error updating area record: " . $stmt->error . "</div>";
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
    <title>Amend Area Record</title>
</head>

<body>
    <h1>Amend Area Record</h1>

    <?php echo $message; ?>

    <div class="container">
        <form method="post">
            <input type="hidden" name="areasno" value="<?php echo htmlspecialchars($row['AREASNO'] ?? ''); ?>">

            <div class="form-group">
                <label for="areasno">Area S.No:</label>
                <input type="text" id="areasno" value="<?php echo htmlspecialchars($row['AREASNO'] ?? ''); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="areaname">Area Name:</label>
                <input type="text" id="areaname" name="areaname" value="<?php echo htmlspecialchars($row['AREANAME'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="areacode">Area Code:</label>
                <input type="text" id="areacode" name="areacode" value="<?php echo htmlspecialchars($row['AREACODE'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="status">Active Status:</label>
                <select id="status" name="status" required>
                    <option value="1" <?php echo ($row['AREAACTFLG'] == 1) ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo ($row['AREAACTFLG'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" name="update" class="btn-update">Update Area</button>
                <a href="../view/areaview.php" class="button">Cancel</a>
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
