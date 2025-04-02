<?php
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $sql = "SELECT USRCODE, USRNAME, USRPASSWORD FROM usemast 
                WHERE USRNAME = '$username' AND USRACTFLG = 1";
        $result = $conn->query($sql);
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['USRPASSWORD'])) {
                $_SESSION['user_id'] = $user['USRCODE'];
                $_SESSION['username'] = $user['USRNAME'];
                $conn->query("UPDATE usemast SET USRLASTLOGIN = NOW() WHERE USRCODE = '{$user['USRCODE']}'");
                header("Location: dashboard.php");
                exit();
            }
        }
    }
    
    // If login fails
    $_SESSION['login_error'] = "Invalid username or password";
    header("Location:./includes/login.php");
    exit();
}
?>