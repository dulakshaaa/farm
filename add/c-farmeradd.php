<?php
include '../includes/connect.php';
require_login();  // Ensure the user is logged in

// Get current user
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM usemast WHERE USRSNO = $user_id");
$current_user = $user_query->fetch_assoc();
$assigned_flosno = $current_user['USRFLOSNO'] ?? null;
$user_role = $_SESSION['role'];  // Get the current user's role (admin or not)

// Get Field Officer name from FLOMAST using USRFLONO for non-admin users
$field_officer_name = '';
if ($assigned_flosno && $user_role !== 'admin') {
    $flo_query = $conn->query("SELECT FLONAME FROM FLOMAST WHERE FLOSNO = $assigned_flosno");
    if ($flo_query && $flo_query->num_rows > 0) {
        $field_data = $flo_query->fetch_assoc();
        $field_officer_name = $field_data['FLONAME'];
    }
}

include '../includes/c-farmernavbar.php';
$area = $conn->query("SELECT AREASNO,AREANAME FROM areamast ORDER BY AREANAME")->fetch_all(MYSQLI_ASSOC);

//--------------------------------------------Add Data----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $farname = $_POST['FARNAME'];
    $farcode = $_POST['FARCODE'];
    $faraddress = $_POST['FARADDRESS'];
    $fartel = $_POST['FARTEL'];
    $faradduser = $_SESSION['username'] ?? 'unknown_user';
    $faraddip = gethostbyname(gethostname());
    $fieldOfficerId = $_POST['FIELD_OFFICER_ID']; // From hidden input
    $areaId = $_POST['areasno'];

    // Handle FARPHOTO
    $farphoto = '';
    if (isset($_FILES['FARPHOTO']) && $_FILES['FARPHOTO']['error'] == 0) {
        $targetDir = "uploads/";
        $farphoto = basename($_FILES['FARPHOTO']['name']);
        $targetFile = $targetDir . $farphoto;

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        move_uploaded_file($_FILES['FARPHOTO']['tmp_name'], $targetFile);
    }

    // Check farm code uniqueness
    $checkSql = "SELECT * FROM FARMA WHERE FARCODE = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $farcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Error: Farm code already taken.');</script>";
    } else {
        $sql = "INSERT INTO FARMA (FARNAME, FARCODE, FARADDRESS, FARTEL, FARPHOTO, FARADDUSER, FARADDIP, FARFLOSNO, FARAREASNO) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssii", $farname, $farcode, $faraddress, $fartel, $farphoto, $faradduser, $faraddip, $fieldOfficerId, $areaId);

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

// Fetch Field Officers for dropdown (only if admin)
$fieldOfficers = [];
if ($user_role === 'admin') {
    $sql = "SELECT FLOSNO, FLONAME FROM FLOMAST ORDER BY FLONAME";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $fieldOfficers[] = $row;
        }
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
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <script>
            $(document).ready(function() {
                const officerNames = <?php echo json_encode($fieldOfficerNames); ?>;

                $("#FLONAME").autocomplete({
                    source: function(request, response) {
                        const term = request.term.toLowerCase();
                        const matches = officerNames.filter(item =>
                            item.toLowerCase().includes(term)
                        );
                        response(matches);
                    },
                    minLength: 1,
                    delay: 100,
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
    <?php endif; ?>

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
                <label for="areasno">Breed</label>
                <select id="areasno" name="areasno" required>
                    <option value="">Select Area</option>
                    <?php foreach ($area as $area): ?>
                        <option value="<?= htmlspecialchars($area['AREASNO']) ?>">
                            <?= htmlspecialchars($area['AREANAME']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
                <label for="FIELD_OFFICER_NAME">Field Officer</label>
                <?php if ($user_role === 'admin'): ?>
                    <select id="FIELD_OFFICER_ID" name="FIELD_OFFICER_ID" required>
                        <option value="">-- Select Field Officer --</option>
                        <?php foreach ($fieldOfficers as $officer): ?>
                            <option value="<?php echo $officer['FLOSNO']; ?>"><?php echo $officer['FLONAME']; ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input type="text" id="FIELD_OFFICER_NAME" value="<?php echo htmlspecialchars($field_officer_name); ?>" readonly>
                    <input type="hidden" name="FIELD_OFFICER_ID" value="<?php echo $assigned_flosno; ?>">
                <?php endif; ?>
            </div>



            <div class="form-actions">
                <button type="reset">Reset</button>
                <button type="submit">Add Record</button>
            </div>
        </form>
    </div>
</body>

</html>