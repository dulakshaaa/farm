<?php
include '../includes/connect.php';  // Include database connection
require_login();  // This will redirect to login if not authenticated

// 2. Get current user data if needed
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM usemast WHERE USRSNO = $user_id");
$current_user = $user_query->fetch_assoc();
include '../includes/areanav.php';

// Fetch existing area names for autocomplete
$areaNames = [];
$nameQuery = "SELECT AREANAME FROM areamast ORDER BY AREANAME";
$nameResult = $conn->query($nameQuery);
if ($nameResult && $nameResult->num_rows > 0) {
    while ($row = $nameResult->fetch_assoc()) {
        $areaNames[] = $row['AREANAME'];
    }
}

//--------------------------------------------Add Area Data-------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $areaname = $_POST['AREANAME'];
    $areacode = $_POST['AREACODE'];
    $adduser = 'Admin'; // Or get from session: $_SESSION['username']
    $addip = gethostbyname(gethostname());

    // Check if area code already exists using prepared statement
    $checkSql = "SELECT * FROM areamast WHERE AREACODE = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $areacode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Error: Area code already exists.');</script>";
    } else {
        // Insert data into the database using prepared statement
        $sql = "INSERT INTO areamast (AREANAME, AREACODE, AREAADDUSER, AREAADDIP, AREAADTIME) 
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $areaname, $areacode, $adduser, $addip);

        if ($stmt->execute()) {
            echo "<script>alert('New Area added successfully'); window.location.href = window.location.href;</script>";
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
    <title>Add New Area</title>
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
            // Autocomplete for Area Name
            const areaNames = <?php echo json_encode($areaNames); ?>;
            
            $("#AREANAME").autocomplete({
                source: function(request, response) {
                    // Filter names based on input
                    const term = request.term.toLowerCase();
                    const matches = areaNames.filter(item => 
                        item.toLowerCase().includes(term)
                    );
                    response(matches);
                },
                minLength: 1,  // Show suggestions after 1 character
                delay: 100,    // 100ms delay after typing
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
            <h1>Add New Area</h1>

            <div class="form-group">
                <label for="AREANAME">Area Name</label>
                <input type="text" id="AREANAME" name="AREANAME" required>
            </div>

            <div class="form-group">
                <label for="AREACODE">Area Code</label>
                <input type="text" id="AREACODE" name="AREACODE" required>
            </div>

            <div class="form-actions">
                <button type="reset">Reset</button>
                <button type="submit">Add Area</button>
            </div>
        </form>
    </div>
</body>
</html>