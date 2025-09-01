<?php
include '../includes/connect.php';
require_login();
require_role('admin');


$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM usemast WHERE USRSNO = $user_id");
$current_user = $user_query->fetch_assoc();
include '../includes/supnav.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supcode = $_POST['supcode'];
    $supnam = $_POST['supnam'];
    $supdes1 = $_POST['supdes1'] ?? '';
    $supdes2 = $_POST['supdes2'] ?? '';
    $adduser = $_SESSION['username'] ?? 'unknown_user';
    $addip = gethostbyname(gethostname());

    $checkSql = "SELECT * FROM supmast WHERE supcode = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $supcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Error: Supplier code already exists.');</script>";
    } else {
        $sql = "INSERT INTO supmast (supcode, supnam, supdes1, supdes2, supadusr, supadip) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $supcode, $supnam, $supdes1, $supdes2, $adduser, $addip);

        if ($stmt->execute()) {
            echo "<script>alert('New Supplier added successfully'); window.location.href = 'view_sup.php';</script>";
        } else {
            echo "<script>alert('Error: " . addslashes($conn->error) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/add.css">
    <title>Add New Supplier</title>
    <style>
        h1::after { background: #3498db; }
    </style>
</head>
<body>
    <div class="form-container">
        <form method="POST" action="">
            <h1>Add New Supplier</h1>

            <div class="form-group">
                <label for="supcode">Supplier Code</label>
                <input type="text" id="supcode" name="supcode" required>
            </div>

            <div class="form-group">
                <label for="supnam">Supplier Name</label>
                <input type="text" id="supnam" name="supnam" required>
            </div>

            <div class="form-group">
                <label for="supdes1">Description 1</label>
                <input type="text" id="supdes1" name="supdes1">
            </div>

            <div class="form-group">
                <label for="supdes2">Description 2</label>
                <input type="text" id="supdes2" name="supdes2">
            </div>

            <div class="form-actions">
                <button type="reset">Reset</button>
                <button type="submit">Add Supplier</button>
            </div>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>