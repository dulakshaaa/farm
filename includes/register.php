<?php
require 'connect.php';
require_login();  // This will redirect to login if not authenticated

// Get current user data if needed
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM usemast WHERE USRSNO = $user_id");
$current_user = $user_query->fetch_assoc();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $usercode = $conn->real_escape_string(trim($_POST['usercode']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($name) || empty($email) || empty($usercode) || empty($password)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } else {
        // Check if email or usercode exists
        $check = $conn->query("SELECT USRSNO FROM usemast WHERE USREMAIL = '$email' OR USRCODE = '$usercode'");
        
        if ($check->num_rows > 0) {
            $error = "Email or User Code already registered";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Get logged-in user's ID for tracking (if applicable)
            $added_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'SYSTEM';
            
            // Capture user IP
            $user_ip = $_SERVER['REMOTE_ADDR'];
            
            // Insert new user (triggers will handle dates/times)
            $sql = "INSERT INTO usemast (USRCODE, USRNAME, USREMAIL, USRPASSWORD, USRADDUSER, USRADDIP) 
                    VALUES ('$usercode', '$name', '$email', '$hashed_password', '$added_by', '$user_ip')";
            
            if ($conn->query($sql)) {
                $success = "Registration successful! Please login.";
                header("refresh:2;url=login.php");
            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS Variables */
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --secondary: #10b981;
            --secondary-dark: #0d9488;
            --accent: #f59e0b;
            --danger: #ef4444;
            --success: #10b981;
            --dark-1: #0f172a;
            --dark-2: #1e293b;
            --dark-3: #334155;
            --light-1: #f8fafc;
            --light-2: #e2e8f0;
            --light-3: #94a3b8;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --transition: background-color 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --radius-sm: 0.25rem;
            --radius: 0.5rem;
        }

        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--dark-1);
            color: var(--light-1);
            font-family: 'Inter', sans-serif;
            line-height: 1.5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .register-container {
            width: 100%;
            max-width: 600px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-light);
            margin-bottom: 0.5rem;
        }

        .register-subtitle {
            color: var(--light-3);
        }

        .register-card {
            background-color: var(--dark-2);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-md);
        }

        /* Messages */
        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            border-left: 4px solid transparent;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border-left-color: var(--success);
        }

        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border-left-color: var(--danger);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--light-3);
            font-weight: 500;
            font-size: 0.9375rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background-color: var(--dark-3);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: var(--radius-sm);
            color: var(--light-1);
            font-size: 0.9375rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }

        /* Button Styles */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-sm);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--light-3);
        }

        .login-link a {
            color: var(--primary-light);
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .register-card {
                padding: 1.5rem;
            }
            
            .register-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1 class="register-title">Create Account</h1>
            <p class="register-subtitle">Join our farm management system</p>
        </div>
        
        <div class="register-card">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="usercode" class="form-label">User Code</label>
                    <input type="text" class="form-control" id="usercode" name="usercode" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required minlength="8">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>
</body>
</html>