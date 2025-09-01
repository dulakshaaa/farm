<?php
require_once '../includes/connect.php';
include '../includes/navbar.php';

// Initialize search variable
$search = '';
$whereClause = '';

// Check if search parameter is set
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $whereClause = " WHERE w.WORKERNAME LIKE '%$search%'";
}

// Modified query to select the desired worker details
$sql = "SELECT 
            w.WORKERSNO,
            w.WORKERNAME,
            w.WORKEREMAIL,
            w.WORKERROLE,
            w.WORKERACTFLG
        FROM workermast w" . $whereClause;
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/view.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <title>Workers Data View</title>
    <style>
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
    </style>
</head>
<body>
    <h1>Workers Data</h1>

    <div class="container">
        <div class="search-container">
            <form method="GET" action="">
                <input type="text" id="workerSearch" name="search" placeholder="Search by Worker Name..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Worker No</th>
                        <th>Worker Name</th>
                        <th>Worker Email</th>
                        <th>Worker Role</th>
                        <th>Active Flag</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['WORKERSNO']); ?></td>
                        <td><?php echo htmlspecialchars($row['WORKERNAME']); ?></td>
                        <td><?php echo htmlspecialchars($row['WORKEREMAIL']); ?></td>
                        <td><?php echo htmlspecialchars($row['WORKERROLE']); ?></td>
                        <td><?php echo htmlspecialchars($row['WORKERACTFLG']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="../amend/workeramend.php?workersno=<?php echo $row['WORKERSNO']; ?>" class="btn btn-update">Update</a>
                                <a href="delete_worker.php?workersno=<?php echo $row['WORKERSNO']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No records found in WORKER table.</p>
        <?php endif; ?>
    </div>

    <!-- jQuery and jQuery UI for autocomplete -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    
    <script>
    $(function() {
        // Autocomplete for worker names
        $("#workerSearch").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "search_workers.php",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                $(this).val(ui.item.value);
                $(this).closest('form').submit();
            }
        });
    });
    </script>

    <?php 
    $conn->close();
    ?>
</body>
</html>
