<?php
require 'connect.php'; // This already starts the session

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usercode = $conn->real_escape_string($_POST['usercode'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($usercode) && !empty($password)) {
        $sql = "SELECT USRSNO, USRNAME, USRPASSWORD, USREMAIL, usrtype FROM usemast 
                WHERE USRCODE = '$usercode' AND USRACTFLG = 1";
        $result = $conn->query($sql);
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['USRPASSWORD'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['USRSNO'];
                $_SESSION['username'] = $user['USRNAME'];
                $_SESSION['usercode'] = $usercode;
                $_SESSION['email'] = $user['USREMAIL'];
                $_SESSION['role'] = $user['usrtype']; // Store role in session
                
                // Redirect based on role
                if ($user['usrtype'] == 'admin') {
                    header("Location:../home.php"); // Redirect admin to home.php
                } else {
                    header("Location: ../c-home.php"); // Redirect user to home2.php
                }
                exit();
            } else {
                $error = "Invalid user code or password";
            }
        } else {
            $error = "Invalid user code or password";
        }
    } else {
        $error = "Please enter both user code and password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <link href="../css/add.css" rel="stylesheet">
    <link href="../css/login.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <h2 class="login-title">Login</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="usercode">User Code</label>
                <input type="text" id="usercode" name="usercode" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
