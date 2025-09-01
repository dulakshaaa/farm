<?php
require_once '../includes/connect.php';
require_login();
include '../includes/stknav.php';

$search = '';
$whereClause = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $whereClause = " WHERE s.stkcode LIKE '%$search%' OR s.stkdesc LIKE '%$search%'";
}

$sql = "SELECT s.stksno, s.stkcode, s.stkdesc, s.stkdes1, 
        DATE_FORMAT(s.stkaddt, '%d-%m-%Y') as add_date 
        FROM stkmast s" . $whereClause;
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/view.css">
    <title>Stock Master</title>
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

        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            font-size: 14px;
        }
        .btn-update {
            background-color: #4CAF50;
        }
        .btn-delete {
            background-color: #f44336;
        }
        .btn:hover {
            opacity: 0.8;
        }
        .search-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .search-container input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 300px;
        }
        .search-container button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .status-active {
            color: green;
        }
        .status-inactive {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Stock Data</h1>

        <div class="search-container">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search by Code or Description..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
                <a href="add_stk.php" class="btn btn-add">Add New</a>
            </form>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Additional Info</th>
                        <th>Added Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['stkcode']) ?></td>
                        <td><?= htmlspecialchars($row['stkdesc']) ?></td>
                        <td><?= htmlspecialchars($row['stkdes1']) ?></td>
                        <td><?= htmlspecialchars($row['add_date']) ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="../amend/amend_stk.php?id=<?= $row['stksno'] ?>" class="btn btn-update">Update</a>
                                <a href="../delete/delete_stk.php?id=<?= $row['stksno'] ?>" class="btn btn-delete" onclick="return confirm('Delete this stock?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No stock records found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>