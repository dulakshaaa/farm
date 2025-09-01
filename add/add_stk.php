<?php
include '../includes/connect.php';
require_login();
require_role('admin');


$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM usemast WHERE USRSNO = $user_id");
$current_user = $user_query->fetch_assoc();
include '../includes//stknav.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stkcode = $_POST['stkcode'];
    $stkdesc = $_POST['stkdesc'];
    $stkdes1 = $_POST['stkdes1'] ?? '';
    $stkdes2 = $_POST['stkdes2'] ?? '';
    $adduser = $_SESSION['username'] ?? 'unknown_user';
    $addip = gethostbyname(gethostname());

    $checkSql = "SELECT * FROM stkmast WHERE stkcode = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $stkcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Error: Stock code already exists.');</script>";
    } else {
        $sql = "INSERT INTO stkmast (stkcode, stkdesc, stkdes1, stkdes2, stkadusr, stkadip) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $stkcode, $stkdesc, $stkdes1, $stkdes2, $adduser, $addip);

        if ($stmt->execute()) {
            echo "<script>alert('New Stock added successfully'); window.location.href = 'view_stk.php';</script>";
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
    <title>Add New Stock</title>
    <style>
        h1::after { background: #e74c3c; }
    </style>
</head>
<body>
    <div class="form-container">
        <form method="POST" action="">
            <h1>Add New Stock</h1>

            <div class="form-group">
                <label for="stkcode">Stock Code</label>
                <input type="text" id="stkcode" name="stkcode" required>
            </div>

            <div class="form-group">
                <label for="stkdesc">Description</label>
                <input type="text" id="stkdesc" name="stkdesc" required>
            </div>

            <div class="form-group">
                <label for="stkdes1">Description 1</label>
                <input type="text" id="stkdes1" name="stkdes1">
            </div>

            <div class="form-group">
                <label for="stkdes2">Description 2</label>
                <input type="text" id="stkdes2" name="stkdes2">
            </div>

            <div class="form-actions">
                <button type="reset">Reset</button>
                <button type="submit">Add Stock</button>
            </div>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>