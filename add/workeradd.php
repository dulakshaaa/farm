<?php
include '../includes/connect.php';  // Include database connection
include '../includes/navbar.php';

//--------------------------------------------Add Data----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $workername = $_POST['WORKERNAME'];
    $workercode = $_POST['WORKERCODE'];
    $workeremail = $_POST['WORKEREMAIL'];
    $workerrole = $_POST['WORKERROLE'];
    $workerphoto = $_FILES['WORKERPHOTO']['name'];
    $ip = gethostbyname(gethostname());  // Get the server's IP address

    // Check if worker code already exists
    $checkSql = "SELECT * FROM workermast WHERE WORKERCODE = '$workercode'";
    $result = $conn->query($checkSql);

    if ($result->num_rows > 0) {
        echo "<script>alert('Error: Worker code already taken.');</script>";
    } else {
        // File upload logic (if you want to save the photo)
        $targetDir = "uploads/";  // Directory to store uploaded files
        $targetFile = $targetDir . basename($workerphoto);
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true); // Create directory if it doesn't exist
        }
        move_uploaded_file($_FILES['WORKERPHOTO']['tmp_name'], $targetFile);

        // Insert data into the database (Note: WORKERSNO is auto-incremented)
        $sql = "INSERT INTO workermast (WORKERNAME, WORKERCODE, WORKEREMAIL, WORKERROLE, WORKERPHOTO, WORKERADDUSER, WORKERADDIP) 
                VALUES ('$workername', '$workercode', '$workeremail', '$workerrole', '$workerphoto', 'Admin', '$ip')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('New worker added successfully');</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}

$conn->close();  // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/add.css"> <!-- Link to your add.css -->
    <title>Add Workermast Record</title>
</head>
<body>
    <h1>Add Workermast Record</h1>
    <div class="form-container">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="WORKERNAME">Worker Name</label>
                <input type="text" id="WORKERNAME" name="WORKERNAME" required>
            </div>

            <div class="form-group">
                <label for="WORKERCODE">Worker Code</label>
                <input type="text" id="WORKERCODE" name="WORKERCODE" required>
            </div>

            <div class="form-group">
                <label for="WORKEREMAIL">Worker Email</label>
                <input type="email" id="WORKEREMAIL" name="WORKEREMAIL" required>
            </div>

            <div class="form-group">
                <label for="WORKERROLE">Worker Role</label>
                <select id="WORKERROLE" name="WORKERROLE" required>
                    <option value="Supervisor">Supervisor</option>
                    <option value="Technician">Technician</option>
                    <option value="Laborer" selected>Laborer</option>
                </select>
            </div>

            <div class="form-group">
                <label for="WORKERPHOTO">Worker Photo</label>
                <input type="file" id="WORKERPHOTO" name="WORKERPHOTO">
            </div>

            <div class="form-actions">
                <button type="reset">Reset</button>
                <button type="submit">Add Record</button>
            </div>
        </form>
    </div>
</body>
</html>
