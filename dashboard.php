<?php
require 'connect.php';
require_login();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Farm Management</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Welcome, <?= htmlspecialchars($_SESSION['username']) ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-body">
                <h2>Dashboard</h2>
                <div class="alert alert-success">
                    You are logged in as <?= htmlspecialchars($_SESSION['email']) ?>
                </div>
                <div class="mt-4">
                    <h4>User Information</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th>User ID</th>
                            <td><?= $current_user['USRSNO'] ?></td>
                        </tr>
                        <tr>
                            <th>User Code</th>
                            <td><?= htmlspecialchars($current_user['USRCODE']) ?></td>
                        </tr>
                        <tr>
                            <th>Registered Date</th>
                            <td><?= $current_user['USRREGISTERDATE'] ?> <?= $current_user['USRREGISTERTIME'] ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>