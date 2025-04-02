<?php
include '../includes/connect.php';
require_login();  // This will redirect to login if not authenticated

// 2. Get current user data if needed
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM usemast WHERE USRSNO = $user_id");
$current_user = $user_query->fetch_assoc();
include '../includes/batchnavbar.php';

// Fetch farms and breeds
$farms = $conn->query("SELECT FARSNO, FARNAME FROM farma ORDER BY FARNAME")->fetch_all(MYSQLI_ASSOC);
$breeds = $conn->query("SELECT BRDSNO, BRDNAME FROM breedmast ORDER BY BRDNAME")->fetch_all(MYSQLI_ASSOC);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $batcode = str_replace('/', '', $_POST['BATCODE']);
    $batfarsno = $_POST['BATFARSNO'];
    $batddt = $_POST['BATDDT'];
    $batbreedsno = $_POST['BATBREEDSNO'];
    $batchicks = $_POST['BATCHICKS'];
    $bataddip = $_SERVER['REMOTE_ADDR'];
    $batlocation = $_POST['BATLOCATION'];

    $stmt = $conn->prepare("INSERT INTO batmast (BATCODE, BATFARSNO, BATDDT, BATBREEDSNO, BATCHICKS, BATADDIP, BATLOCATION) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiss", $batcode, $batfarsno, $batddt, $batbreedsno, $batchicks, $bataddip, $batlocation);
    
    if ($stmt->execute()) {
        $success = "Batch saved successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    
    <style>
        h1 {
            color: #2c3e50;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 2.5em;
            margin: 0 0 30px 0;
            text-align: left;
            position: relative;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100px;
            height: 3px;
            background: #3498db;
            border-radius: 2px;
        }
    </style>
    
    <link rel="stylesheet" href="../css/add.css">
    <title>Add Batch</title>
</head>
<body>
    <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    
    <div class="form-container">
        <form method="POST" action="">
            <h1>Add Batch Record</h1>
            
            <div class="form-group">
                <label for="batcode">Batch Code</label>
                <input type="text" id="batcode" name="BATCODE" value="____/__/__" maxlength="10" required>
            </div>
            
            <div class="form-group">
                <label for="batfarsno">Farm</label>
                <select id="batfarsno" name="BATFARSNO" required>
                    <option value="">Select Farm</option>
                    <?php foreach ($farms as $farm): ?>
                        <option value="<?= htmlspecialchars($farm['FARSNO']) ?>">
                            <?= htmlspecialchars($farm['FARNAME']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="batddt">Batch Date</label>
                <input type="date" id="batddt" name="BATDDT" required>
            </div>
            
            <div class="form-group">
                <label for="batbreedsno">Breed</label>
                <select id="batbreedsno" name="BATBREEDSNO" required>
                    <option value="">Select Breed</option>
                    <?php foreach ($breeds as $breed): ?>
                        <option value="<?= htmlspecialchars($breed['BRDSNO']) ?>">
                            <?= htmlspecialchars($breed['BRDNAME']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="batchicks">Number of Chicks</label>
                <input type="number" id="batchicks" name="BATCHICKS" required>
            </div>
            
            <div class="form-group">
                <label for="batlocation">Location</label>
                <input type="text" id="batlocation" name="BATLOCATION" readonly>
            </div>
            
            <div class="form-actions">
                <button type="reset">Reset</button>
                <button type="submit" style="background-color: #4CAF50; color: white; border: none;">Save</button>
            </div>
        </form>
    </div>

    <script>
        // Format BATCODE input
        document.getElementById('batcode').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^A-Za-z0-9]/g, "");
            let formatted = value;
            
            if (value.length > 4) {
                formatted = value.slice(0, 4) + '/' + value.slice(4);
            }
            if (value.length > 6) {
                formatted = value.slice(0, 4) + '/' + value.slice(4, 6) + '/' + value.slice(6);
            }
            
            e.target.value = formatted;
        });

        // Get current location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    document.getElementById('batlocation').value = 
                        `Lat:${position.coords.latitude.toFixed(6)}, Long:${position.coords.longitude.toFixed(6)}`;
                },
                function() {
                    document.getElementById('batlocation').value = 'Location not available';
                }
            );
        } else {
            document.getElementById('batlocation').value = 'Geolocation not supported';
        }
    </script>
</body>
</html>