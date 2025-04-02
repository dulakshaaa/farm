<?php
require_once '../includes/connect.php';  // Include database connection
include '../includes/farmnavbar.php';

// Initialize variables
$message = '';
$farsno = isset($_GET['id']) ? $_GET['id'] : '';

// Fetch farm data if ID is provided
if ($farsno) {
    $stmt = $conn->prepare("SELECT 
                            f.FARSNO, 
                            f.FARNAME, 
                            f.FARCODE, 
                            f.FARADDRESS, 
                            f.FARTEL, 
                            f.FARPHOTO, 
                            f.FARACTFLG
                          FROM FARMA f
                          WHERE f.FARSNO = ?");
    $stmt->bind_param("i", $farsno);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $farsno = $_POST['farsno'];
    $farmname = trim($_POST['farmname']);
    $farmcode = trim($_POST['farmcode']);
    $farmaddress = trim($_POST['farmaddress']);
    $farmtel = trim($_POST['farmtel']);
    $farmstatus = $_POST['farmstatus']; // Get the selected status value
    $photo = $_FILES['farmphoto'];

    // Validate inputs
    if (empty($farmname) || empty($farmcode)) {
        $message = "<div class='error'>Farm name and code are required!</div>";
    } else {
        // Handle file upload
        $newPhoto = $row['FARPHOTO'];  // Keep the current photo by default
        if ($photo['error'] === UPLOAD_ERR_OK) {
            $targetDir = "../uploads/";
            $targetFile = $targetDir . basename($photo["name"]);
            move_uploaded_file($photo["tmp_name"], $targetFile);
            $newPhoto = basename($photo["name"]);
        }

        $stmt = $conn->prepare("UPDATE FARMA SET 
                              FARNAME = ?, 
                              FARCODE = ?, 
                              FARADDRESS = ?, 
                              FARTEL = ?, 
                              FARPHOTO = ?, 
                              FARACTFLG = ?
                              WHERE FARSNO = ?");

        $stmt->bind_param("sssssii", $farmname, $farmcode, $farmaddress, $farmtel, $newPhoto, $farmstatus, $farsno);

        if ($stmt->execute()) {
            $message = "<div class='success'>Farm record updated successfully!</div>";
            
            // Refresh the data after update
            $stmt = $conn->prepare("SELECT 
                                    f.FARSNO, 
                                    f.FARNAME, 
                                    f.FARCODE, 
                                    f.FARADDRESS, 
                                    f.FARTEL, 
                                    f.FARPHOTO, 
                                    f.FARACTFLG
                                  FROM FARMA f
                                  WHERE f.FARSNO = ?");
            $stmt->bind_param("i", $farsno);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
        } else {
            $message = "<div class='error'>Error updating farm record: " . $stmt->error . "</div>";
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
    <title>Amend Farm Record</title>
</head>

<body>
    <h1>Amend Farm Record</h1>

    <?php echo $message; ?>

    <div class="container">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="farsno" value="<?php echo htmlspecialchars($row['FARSNO'] ?? ''); ?>">

            <div class="form-group">
                <label for="farsno">Farm S.No:</label>
                <input type="text" id="farsno" value="<?php echo htmlspecialchars($row['FARSNO'] ?? ''); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="farmname">Farm Name:</label>
                <input type="text" id="farmname" name="farmname" value="<?php echo htmlspecialchars($row['FARNAME'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="farmcode">Farm Code:</label>
                <input type="text" id="farmcode" name="farmcode" value="<?php echo htmlspecialchars($row['FARCODE'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="farmaddress">Farm Address:</label>
                <input type="text" id="farmaddress" name="farmaddress" value="<?php echo htmlspecialchars($row['FARADDRESS'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="farmtel">Phone:</label>
                <input type="text" id="farmtel" name="farmtel" value="<?php echo htmlspecialchars($row['FARTEL'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="farmphoto">Farm Photo:</label>
                <input type="file" id="farmphoto" name="farmphoto">
                <?php if ($row['FARPHOTO']): ?>
                    <p>Current Photo: <img src="../uploads/<?php echo htmlspecialchars($row['FARPHOTO']); ?>" alt="Farm Photo" width="100"></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="farmstatus">Active Status:</label>
                <select id="farmstatus" name="farmstatus" required>
                    <option value="1" <?php echo ($row['FARACTFLG'] == 1) ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo ($row['FARACTFLG'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" name="update" class="btn-update">Update Farm</button>
                <a href="../view/farmview.php" class="button">Cancel</a>
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
