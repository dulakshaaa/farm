<?php
include '../includes/connect.php';  // Include database connection
require_login();  // This will redirect to login if not authenticated

// 2. Get current user data if needed
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM usemast WHERE USRSNO = $user_id");
$current_user = $user_query->fetch_assoc();
include '../includes/farmnavbar.php';

//--------------------------------------------Add Data----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $farname = $_POST['FARNAME'];
    $farcode = $_POST['FARCODE'];
    $faraddress = $_POST['FARADDRESS'];
    $fartel = $_POST['FARTEL'];
    $faradduser = 'Admin';
    $faraddip = gethostbyname(gethostname());
    $fieldOfficerId = $_POST['FIELD_OFFICER_ID'];  // Field Officer ID selected from the dropdown

    // Handle file upload for FARPHOTO
    $farphoto = '';
    if (isset($_FILES['FARPHOTO']) && $_FILES['FARPHOTO']['error'] == 0) {
        $targetDir = "uploads/";
        $farphoto = basename($_FILES['FARPHOTO']['name']);
        $targetFile = $targetDir . $farphoto;

        // Ensure the upload directory exists
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        move_uploaded_file($_FILES['FARPHOTO']['tmp_name'], $targetFile);
    }

    // Check if farm code already exists using prepared statement
    $checkSql = "SELECT * FROM FARMA WHERE FARCODE = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $farcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Error: Farm code already taken.');</script>";
    } else {
        // Insert data into the database using prepared statement, including FIELD_OFFICER_ID
        $sql = "INSERT INTO FARMA (FARNAME, FARCODE, FARADDRESS, FARTEL, FARPHOTO, FARADDUSER, FARADDIP, FARFLOSNO) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $farname, $farcode, $faraddress, $fartel, $farphoto, $faradduser, $faraddip, $fieldOfficerId);

        if ($stmt->execute()) {
            echo "<script>alert('New farm added successfully'); window.location.href = window.location.href;</script>";
        } else {
            echo "<script>alert('Error: " . addslashes($conn->error) . "');</script>";
        }
        $stmt->close();
    }
}

// Fetch existing farm names for autocomplete
$farmNames = [];
$sql = "SELECT FARNAME FROM FARMA ORDER BY FARNAME";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $farmNames[] = $row['FARNAME'];
    }
}

// Fetch Field Officers for dropdown (from FLOMAST)
$fieldOfficers = [];
$sql = "SELECT FLOSNO, FLONAME FROM FLOMAST ORDER BY FLONAME";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fieldOfficers[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/add.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <title>Add FARM Record</title>
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
        .autocomplete-input {
            position: relative;
            width: 200px;
        }
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
        }
        .ui-menu-item-wrapper {
            padding: 5px;
        }
        .ui-state-active {
            background-color: #e0f7fa;
        }
    </style>
    <script>
        $(document).ready(function() {
            const farmNames = <?php echo json_encode($farmNames); ?>;
            $('#FARNAME').autocomplete({
                source: function(request, response) {
                    const term = request.term.toLowerCase();
                    const matches = $.grep(farmNames, function(item) {
                        return item.toLowerCase().indexOf(term) === 0;
                    });
                    response(matches);
                },
                minLength: 1,
                select: function(event, ui) {
                    $(this).val(ui.item.value);
                    return false;
                },
                focus: function(event, ui) {
                    $(this).val(ui.item.value);
                    return false;
                }
            });
        });
    </script>
</head>
<body>
    <div class="form-container">
        <form method="POST" action="" enctype="multipart/form-data">
            <h1>Add Farmer Record</h1>

            <div class="form-group">
                <label for="FARNAME">Farm Name</label>
                <input type="text" id="FARNAME" name="FARNAME" class="autocomplete-input" required>
            </div>

            <div class="form-group">
                <label for="FARCODE">Farm Code</label>
                <input type="text" id="FARCODE" name="FARCODE" required>
            </div>

            <div class="form-group">
                <label for="FARADDRESS">Farm Address</label>
                <textarea id="FARADDRESS" name="FARADDRESS" required></textarea>
            </div>

            <div class="form-group">
                <label for="FARTEL">Farm Telephone</label>
                <input type="text" id="FARTEL" name="FARTEL" required>
            </div>

            <div class="form-group">
                <label for="FARPHOTO">Farm Photo</label>
                <input type="file" id="FARPHOTO" name="FARPHOTO" accept="image/*">
            </div>

            <div class="form-group">
                <label for="FIELD_OFFICER_ID">Select Field Officer</label>
                <select id="FIELD_OFFICER_ID" name="FIELD_OFFICER_ID" required>
                    <option value="">-- Select Field Officer --</option>
                    <?php foreach ($fieldOfficers as $officer): ?>
                        <option value="<?php echo $officer['FLOSNO']; ?>"><?php echo $officer['FLONAME']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="reset">Reset</button>
                <button type="submit">Add Record</button>
            </div>
        </form>
    </div>
</body>
</html>
