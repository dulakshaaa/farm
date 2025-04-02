<?php 
include '../includes/connect.php';  // Include database connection
require_login();  // This will redirect to login if not authenticated

// 2. Get current user data if needed
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM usemast WHERE USRSNO = $user_id");
$current_user = $user_query->fetch_assoc();
include '../includes/breednavbar.php';  

//--------------------------------------------Add Data----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {     
    // Use prepared statements to prevent SQL injection
    $brdname = $_POST['BRDNAME'];     
    $brdcode = $_POST['BRDCODE'];     
    $brdadduser = 'Admin'; 
    $brdaddip = gethostbyname(gethostname());  

    // Check for existing breed code using prepared statement
    $checkSql = "SELECT * FROM breedmast WHERE BRDCODE = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $brdcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {         
        echo "<script>alert('Error: Breed code already taken.');</script>";     
    } else {         
        $sql = "INSERT INTO breedmast (BRDNAME, BRDCODE, BRDADDUSER, BRDADDIP) VALUES (?, ?, ?, ?)";         
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $brdname, $brdcode, $brdadduser, $brdaddip);

        if ($stmt->execute()) {             
            echo "<script>alert('New breed added successfully'); window.location.href = window.location.href;</script>";         
        } else {             
            echo "<script>alert('Error: " . addslashes($conn->error) . "');</script>";         
        }     
        $stmt->close();
    } 
} 

// Fetch existing breed names for autocomplete
$breedNames = [];
$sql = "SELECT BRDNAME FROM breedmast ORDER BY BRDNAME";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $breedNames[] = $row['BRDNAME'];
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
    <title>Add BREEDMAST Record</title>
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
            z-index: 1000; /* Ensure it appears above other elements */
        }
        .ui-menu-item-wrapper {
            padding: 5px;
        }
        .ui-state-active {
            background-color: #e0f7fa; /* Light cyan for selected item */
        }
    </style>
    <script>
        $(document).ready(function() {
            // Geolocation code
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude.toFixed(6);
                        const lon = position.coords.longitude.toFixed(6);
                        const locationStr = `Lat:${lat},Long:${lon}`;
                        $('#BRDLOCATION').val(locationStr);
                    },
                    function(error) {
                        console.error('Geolocation error:', error);
                        $('#BRDLOCATION').val('Lat:Unknown,Long:Unknown');
                    }
                );
            } else {
                $('#BRDLOCATION').val('Geolocation not supported');
            }

            // Autocomplete using jQuery UI
            const breedNames = <?php echo json_encode($breedNames); ?>;
            $('#BRDNAME').autocomplete({
                source: function(request, response) {
                    // Filter breed names based on input
                    const term = request.term.toLowerCase();
                    const matches = $.grep(breedNames, function(item) {
                        return item.toLowerCase().indexOf(term) === 0; // Starts with
                    });
                    response(matches);
                },
                minLength: 1, // Start suggesting after 1 character
                select: function(event, ui) {
                    // When an item is selected, set the input value
                    $(this).val(ui.item.value);
                    return false; // Prevent default behavior
                },
                focus: function(event, ui) {
                    // On focus (hover), show the full suggestion
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
            <h1>Add Breed Record</h1>
            
            <div class="form-group">
                <label for="BRDNAME">Breed Name</label>
                <input type="text" id="BRDNAME" name="BRDNAME" class="autocomplete-input" required>
            </div>

            <div class="form-group">
                <label for="BRDCODE">Breed Code</label>
                <input type="text" id="BRDCODE" name="BRDCODE" required>
            </div>

            <div class="form-group">
                <label for="BRDLOCATION">Location</label>
                <input type="text" id="BRDLOCATION" name="BRDLOCATION" readonly>
            </div>

            <div class="form-actions">
                <button type="reset">Reset</button>
            <button type="submit">Add Record</button>
            </div>
        </form>
    </div>
</body>
</html>