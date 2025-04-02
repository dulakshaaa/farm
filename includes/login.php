<?php
require 'connect.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location:../home.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        $sql = "SELECT USRSNO, USRNAME, USRPASSWORD FROM usemast 
                WHERE USREMAIL = '$email' AND USRACTFLG = 1";
        $result = $conn->query($sql);
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['USRPASSWORD'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['USRSNO'];
                $_SESSION['username'] = $user['USRNAME'];
                $_SESSION['email'] = $email;
                
                // Redirect to intended page or dashboard
                header("Location: " . ($_SESSION['redirect_url'] ?? '../home.php'));
                exit();
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Please enter both email and password";
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
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
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