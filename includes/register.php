<?php
require 'connect.php';


$error = '';
$success = '';

// Fetch field officers for dropdown
$field_officers_result = $conn->query("SELECT FLOSNO, FLONAME FROM flomast");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string(trim($_POST['email']));
    $usercode = $conn->real_escape_string(trim($_POST['usercode']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $usrtype = $conn->real_escape_string($_POST['usrtype']);

    if ($usrtype === 'admin') {
        $name = $conn->real_escape_string(trim($_POST['name']));
        $usrflosno = 'NULL';
    } elseif ($usrtype === 'user') {
        $usrflosno = intval($_POST['name_dropdown']);
        $fo_check = $conn->query("SELECT FLONAME FROM flomast WHERE FLOSNO = $usrflosno");
        if ($fo_check->num_rows === 0) {
            $error = "Invalid Field Officer selected";
        } else {
            $row = $fo_check->fetch_assoc();
            $name = $conn->real_escape_string($row['FLONAME']);
        }
    } else {
        $error = "Invalid user type selected";
    }

    // Proceed if no errors yet
    if (empty($error)) {
        if (empty($name) || empty($email) || empty($usercode) || empty($password) || empty($usrtype)) {
            $error = "All fields are required";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters";
        } else {
            $check = $conn->query("SELECT USRSNO FROM usemast WHERE USREMAIL = '$email' OR USRCODE = '$usercode'");
            if ($check->num_rows > 0) {
                $error = "Email or User Code already registered";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $added_by = $_SESSION['username'] ?? 'unknown_user';
                $user_ip = $_SERVER['REMOTE_ADDR'];

                $sql = "INSERT INTO usemast (USRCODE, USRNAME, USREMAIL, USRPASSWORD, USRADDUSER, USRADDIP, USRTYPE, USRFLOSNO) 
                        VALUES ('$usercode', '$name', '$email', '$hashed_password', '$added_by', '$user_ip', '$usrtype', $usrflosno)";

                if ($conn->query($sql)) {
                    $success = "Registration successful! Please login.";
                    header("refresh:2;url=login.php");
                } else {
                    $error = "Registration failed: " . $conn->error;
                }
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
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --radius-sm: 0.25rem;
        --radius: 0.5rem;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background-color: var(--dark-1);
        color: var(--light-1);
        font-family: 'Inter', sans-serif;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 2rem;
        line-height: 1.5;
    }

    .register-container {
        background-color: var(--dark-2);
        padding: 2.5rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
        width: 100%;
        max-width: 500px;
        animation: fadeIn 0.5s ease forwards;
    }

    h2 {
        font-family: 'Montserrat', sans-serif;
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        color: var(--primary-light);
        text-align: center;
    }

    .alert {
        padding: 1rem;
        border-radius: var(--radius-sm);
        margin-bottom: 1.5rem;
        border-left: 4px solid;
    }

    .alert-danger {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border-color: var(--danger);
    }

    .alert-success {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border-color: var(--success);
    }

    .form-group {
        margin-bottom: 1.25rem;
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

    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
    }

    .btn {
        width: 100%;
        padding: 0.75rem;
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    p {
        margin-top: 1.5rem;
        text-align: center;
        color: var(--light-3);
        font-size: 0.9375rem;
    }

    a {
        color: var(--primary-light);
        text-decoration: none;
        transition: var(--transition);
    }

    a:hover {
        color: var(--primary);
        text-decoration: underline;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        body {
            padding: 1rem;
        }
        
        .register-container {
            padding: 1.5rem;
        }
        
        h2 {
            font-size: 1.5rem;
        }
    }
</style>
</head>
<body>
    <div class="register-container">
        <h2>Create Account</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="usrtype" class="form-label">User Type</label>
                <select class="form-control" id="usrtype" name="usrtype" required>
                    <option value="">Select User Type</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>

            <!-- Text input for Admin -->
            <div class="form-group" id="nameTextInput">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name">
            </div>

            <!-- Dropdown for User -->
            <div class="form-group" id="nameDropdown" style="display: none;">
                <label for="name_dropdown" class="form-label">Select Field Officer</label>
                <select class="form-control" id="name_dropdown" name="name_dropdown">
                    <option value="">Select Field Officer</option>
                    <?php while ($row = $field_officers_result->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['FLOSNO']) ?>">
                            <?= htmlspecialchars($row['FLONAME']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
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

            <button type="submit" class="btn"><i class="fas fa-user-plus"></i> Register</button>
        </form>

        <p>Already have an account? <a href="./login.php">Login here</a></p>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const userType = document.getElementById('usrtype');
        const nameText = document.getElementById('nameTextInput');
        const nameDrop = document.getElementById('nameDropdown');

        userType.addEventListener('change', function () {
            if (this.value === 'admin') {
                nameText.style.display = 'block';
                nameDrop.style.display = 'none';
            } else if (this.value === 'user') {
                nameText.style.display = 'none';
                nameDrop.style.display = 'block';
            } else {
                nameText.style.display = 'none';
                nameDrop.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html>
