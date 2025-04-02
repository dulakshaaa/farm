<?php
require_once '../includes/connect.php';  // Include database connection
include '../includes/fieldoffnavbar.php';

// Initialize variables
$message = '';
$flosno = isset($_GET['id']) ? $_GET['id'] : '';

// Fetch field officer data if ID is provided
if ($flosno) {
    $stmt = $conn->prepare("SELECT 
                            f.FLOSNO, 
                            f.FLONAME, 
                            f.FLOCODE, 
                            f.FLOTEL, 
                            f.FLOAREASNO,
                            a.AREANAME,
                            f.FLOACTFLG
                          FROM FLOMAST f
                          LEFT JOIN areamast a ON f.FLOAREASNO = a.AREASNO
                          WHERE f.FLOSNO = ?");
    $stmt->bind_param("i", $flosno);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
}

// Fetch areas for dropdown
$areas = [];
$areaQuery = "SELECT AREASNO, AREANAME FROM areamast ORDER BY AREANAME";
$areaResult = $conn->query($areaQuery);
if ($areaResult) {
    while ($areaRow = $areaResult->fetch_assoc()) {
        $areas[$areaRow['AREASNO']] = $areaRow['AREANAME'];
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $flosno = $_POST['flosno'];
    $floname = trim($_POST['floname']);
    $flocode = trim($_POST['flocode']);
    $flotel = trim($_POST['flotel']);
    $floareasno = $_POST['floareasno'];
    $flostatus = $_POST['flostatus'];

    // Validate inputs
    if (empty($floname) || empty($flocode)) {
        $message = "<div class='error'>Field officer name and code are required!</div>";
    } else {
        $stmt = $conn->prepare("UPDATE FLOMAST SET 
                              FLONAME = ?, 
                              FLOCODE = ?, 
                              FLOTEL = ?, 
                              FLOAREASNO = ?, 
                              FLOACTFLG = ?
                              WHERE FLOSNO = ?");

        $stmt->bind_param("sssiii", $floname, $flocode, $flotel, $floareasno, $flostatus, $flosno);

        if ($stmt->execute()) {
            $message = "<div class='success'>Field officer record updated successfully!</div>";
            
            // Refresh the data after update
            $stmt = $conn->prepare("SELECT 
                                    f.FLOSNO, 
                                    f.FLONAME, 
                                    f.FLOCODE, 
                                    f.FLOTEL, 
                                    f.FLOAREASNO,
                                    a.AREANAME,
                                    f.FLOACTFLG
                                  FROM FLOMAST f
                                  LEFT JOIN areamast a ON f.FLOAREASNO = a.AREASNO
                                  WHERE f.FLOSNO = ?");
            $stmt->bind_param("i", $flosno);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
        } else {
            $message = "<div class='error'>Error updating field officer record: " . $stmt->error . "</div>";
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
    <title>Amend Field Officer Record</title>
    <style>
        .success {
            color: green;
            background-color: #e8f5e9;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            transition: opacity 1s ease-out;
        }
        .error {
            color: red;
            background-color: #ffebee;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .fade-out {
            opacity: 0;
        }
    </style>
</head>

<body>
    <h1>Amend Field Officer Record</h1>

    <?php echo $message; ?>

    <div class="container">
        <form method="post">
            <input type="hidden" name="flosno" value="<?php echo htmlspecialchars($row['FLOSNO'] ?? ''); ?>">

            <div class="form-group">
                <label for="flosno">Officer S.No:</label>
                <input type="text" id="flosno" value="<?php echo htmlspecialchars($row['FLOSNO'] ?? ''); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="floname">Officer Name:</label>
                <input type="text" id="floname" name="floname" value="<?php echo htmlspecialchars($row['FLONAME'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="flocode">Officer Code:</label>
                <input type="text" id="flocode" name="flocode" value="<?php echo htmlspecialchars($row['FLOCODE'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="flotel">Phone:</label>
                <input type="text" id="flotel" name="flotel" value="<?php echo htmlspecialchars($row['FLOTEL'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="floareasno">Assigned Area:</label>
                <select id="floareasno" name="floareasno" required>
                    <option value="">-- Select Area --</option>
                    <?php foreach ($areas as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo ($id == ($row['FLOAREASNO'] ?? '')) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="flostatus">Active Status:</label>
                <select id="flostatus" name="flostatus" required>
                    <option value="1" <?php echo ($row['FLOACTFLG'] == 1) ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo ($row['FLOACTFLG'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" name="update" class="btn-update">Update Officer</button>
                <a href="../view/floview.php" class="button">Cancel</a>
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