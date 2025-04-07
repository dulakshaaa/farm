<?php
require_once '../includes/connect.php';
include '../includes/batchnavbar.php';

// Initialize variables
$message = '';
$batcode = isset($_GET['batcode']) ? $_GET['batcode'] : '';

// Fetch record data if batcode is provided
if ($batcode) {
    $stmt = $conn->prepare("SELECT 
                            b.BATCODE, 
                            b.BATFARSNO, 
                            f.FARNAME,
                            b.BATDDT, 
                            b.BATBREEDSNO,
                            br.BRDNAME,
                            b.BATCHICKS, 
                            b.BATACTFLG 
                          FROM BATMAST b
                          LEFT JOIN FARMA f ON b.BATFARSNO = f.FARSNO
                          LEFT JOIN BREEDMAST br ON b.BATBREEDSNO = br.BRDSNO
                          WHERE b.BATCODE = ?");
    $stmt->bind_param("s", $batcode);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    // Fetch all farms for dropdown
    $farms = $conn->query("SELECT FARSNO, FARNAME FROM FARMA");
    // Fetch all breeds for dropdown
    $breeds = $conn->query("SELECT BRDSNO, BRDNAME FROM BREEDMAST");
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $batcode = $_POST['batcode'];
    $batfarsno = $_POST['batfarsno'];
    $batddt = $_POST['batddt'];
    $batbreedsno = $_POST['batbreedsno'];
    $batchicks = $_POST['batchicks'];
    
    // Convert the batch status to 0 or 1
    $batactflg = ($_POST['batactflg'] === '1') ? '1' : '0'; // Set '1' for Active, '0' for Inactive

    // Get the user's information
    
    $user = $_SESSION['username'] ?? 'unknown_user';  // Replace with actual user info

    // Get the user's IP address
    $user_ip = $_SERVER['REMOTE_ADDR'];

    // Get the current date and time
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');

    $stmt = $conn->prepare("UPDATE BATMAST SET 
                          BATFARSNO = ?, 
                          BATDDT = ?, 
                          BATBREEDSNO = ?, 
                          BATCHICKS = ?, 
                          BATACTFLG = ?, 
                          BATAMDDUSER = ?, 
                          BATAMDDIP = ?, 
                          BATAMDDT = ?, 
                          BATAMDTIME = ?
                          WHERE BATCODE = ?");

    $stmt->bind_param("ssssssssss", $batfarsno, $batddt, $batbreedsno, $batchicks, $batactflg, $user, $user_ip, $current_date, $current_time, $batcode);

    if ($stmt->execute()) {
        $message = "<div class='success'>Batch record updated successfully!</div>";

        // Refresh the data after update
        $stmt = $conn->prepare("SELECT 
                                b.BATCODE, 
                                b.BATFARSNO, 
                                f.FARNAME,
                                b.BATDDT, 
                                b.BATBREEDSNO,
                                br.BRDNAME,
                                b.BATCHICKS, 
                                b.BATACTFLG 
                              FROM BATMAST b
                              LEFT JOIN FARMA f ON b.BATFARSNO = f.FARSNO
                              LEFT JOIN BREEDMAST br ON b.BATBREEDSNO = br.BRDSNO
                              WHERE b.BATCODE = ?");
        $stmt->bind_param("s", $batcode);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
    } else {
        $message = "<div class='error'>Error updating batch record: " . $stmt->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/amend.css">
    <title>Amend Batch Record</title>
</head>

<body>
    <h1>Amend Batch Record</h1>

    <?php echo $message; ?>

    <div class="container">
        <form method="post">
            <input type="hidden" name="batcode" value="<?php echo htmlspecialchars($row['BATCODE'] ?? ''); ?>">

            <div class="form-group">
                <label for="batcode">Batch Code:</label>
                <input type="text" id="batcode" value="<?php echo htmlspecialchars($row['BATCODE'] ?? ''); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="batfarsno">Farm:</label>
                <select id="batfarsno" name="batfarsno" required>
                    <?php while ($farm = $farms->fetch_assoc()): ?>
                        <option value="<?php echo $farm['FARSNO']; ?>" <?php echo (isset($row['BATFARSNO']) && $row['BATFARSNO'] == $farm['FARSNO']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($farm['FARNAME']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="batddt">Batch Date:</label>
                <input type="date" id="batddt" name="batddt" value="<?php echo htmlspecialchars($row['BATDDT'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="batbreedsno">Breed:</label>
                <select id="batbreedsno" name="batbreedsno" required>
                    <?php while ($breed = $breeds->fetch_assoc()): ?>
                        <option value="<?php echo $breed['BRDSNO']; ?>" <?php echo (isset($row['BATBREEDSNO']) && $row['BATBREEDSNO'] == $breed['BRDSNO']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($breed['BRDNAME']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="batchicks">Number of Chicks:</label>
                <input type="number" id="batchicks" name="batchicks" value="<?php echo htmlspecialchars($row['BATCHICKS'] ?? ''); ?>" required min="0">
            </div>

            <div class="form-group">
                <label for="batactflg">Batch Status:</label>
                <select id="batactflg" name="batactflg" required>
                    <option value="1" <?php echo (isset($row['BATACTFLG']) && $row['BATACTFLG'] == '1') ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo (isset($row['BATACTFLG']) && $row['BATACTFLG'] == '0') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" name="update" class="btn-update">Update Batch</button>
                <a href="../view/batches.php" class="button">Cancel</a>
            </div>
        </form>
    </div>
</body>
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

</html>

<?php
$conn->close();
?>
