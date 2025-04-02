<?php
include '../includes/connect.php';  // Include database connection
require_login();  // This will redirect to login if not authenticated

// 2. Get current user data if needed
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM usemast WHERE USRSNO = $user_id");
$current_user = $user_query->fetch_assoc();
include '../includes/fieldoffnavbar.php';

// Fetch areas for dropdown
$areas = [];
$areaQuery = "SELECT AREASNO, AREANAME FROM areamast ORDER BY AREANAME";
$areaResult = $conn->query($areaQuery);
if ($areaResult) {
    while ($row = $areaResult->fetch_assoc()) {
        $areas[$row['AREASNO']] = $row['AREANAME'];
    }
}

// Fetch existing field officer names for autocomplete
$fieldOfficerNames = [];
$nameQuery = "SELECT FLONAME FROM FLOMAST ORDER BY FLONAME";
$nameResult = $conn->query($nameQuery);
if ($nameResult && $nameResult->num_rows > 0) {
    while ($row = $nameResult->fetch_assoc()) {
        $fieldOfficerNames[] = $row['FLONAME'];
    }
}

//--------------------------------------------Add Data----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $floname = $_POST['FLONAME'];
    $flocode = $_POST['FLOCODE'];
    $flotel = $_POST['FLOTEL'];
    $floareasno = $_POST['FLOAREASNO'];
    $floadduser = 'Admin';
    $floaddip = gethostbyname(gethostname());

    // Check if officer code already exists using prepared statement
    $checkSql = "SELECT * FROM FLOMAST WHERE FLOCODE = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $flocode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Error: Field Officer code already taken.');</script>";
    } else {
        // Insert data into the database using prepared statement
        $sql = "INSERT INTO FLOMAST (FLONAME, FLOCODE, FLOTEL, FLOAREASNO, FLOADDUSER, FLOADDIP) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssiss", $floname, $flocode, $flotel, $floareasno, $floadduser, $floaddip);

        if ($stmt->execute()) {
            echo "<script>alert('New Field Officer added successfully'); window.location.href = window.location.href;</script>";
        } else {
            echo "<script>alert('Error: " . addslashes($conn->error) . "');</script>";
        }
        $stmt->close();
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
    <!-- Include jQuery and jQuery UI for autocomplete -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <title>Add Field Officer</title>
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

        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            margin-top: 5px;
        }

        /* Autocomplete styling */
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .ui-menu-item {
            padding: 8px 12px;
            font-family: inherit;
            cursor: pointer;
        }
        
        .ui-menu-item:hover, .ui-state-active {
            background-color: #f0f0f0;
        }
    </style>
    <script>
        $(document).ready(function() {
            // Autocomplete for Field Officer Name
            const officerNames = <?php echo json_encode($fieldOfficerNames); ?>;
            
            $("#FLONAME").autocomplete({
                source: function(request, response) {
                    // Filter names based on input
                    const term = request.term.toLowerCase();
                    const matches = officerNames.filter(item => 
                        item.toLowerCase().includes(term)
                    );
                    response(matches);
                },
                minLength: 1,  // Show suggestions after 1 character
                delay: 100,   // 100ms delay after typing
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
        <form method="POST" action="">
            <h1>Add Field Officer Record</h1>

            <div class="form-group">
                <label for="FLONAME">Field Officer Name</label>
                <input type="text" id="FLONAME" name="FLONAME" required>
            </div>

            <div class="form-group">
                <label for="FLOCODE">Field Officer Code</label>
                <input type="text" id="FLOCODE" name="FLOCODE" required>
            </div>

            <div class="form-group">
                <label for="FLOTEL">Field Officer Telephone</label>
                <input type="text" id="FLOTEL" name="FLOTEL" required>
            </div>

            <div class="form-group">
                <label for="FLOAREASNO">Assigned Area</label>
                <select id="FLOAREASNO" name="FLOAREASNO" required>
                    <option value="">-- Select Area --</option>
                    <?php foreach ($areas as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
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