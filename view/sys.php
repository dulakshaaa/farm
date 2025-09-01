<?php
require_once '../includes/connect.php';
require_login();  // This will redirect to login if not authenticated

// Get current user data
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM usemast WHERE USRSNO = $user_id");
$current_user = $user_query->fetch_assoc();
include '../includes/sysaddnav.php';

// Initialize search variable
$search = '';
$whereClause = '';

// Check if search parameter is set
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $whereClause = " WHERE s.sysdes1 LIKE '%$search%' OR s.sysrtp LIKE '%$search%' OR s.sysrno LIKE '%$search%'";
}

// Query for sysmast table
$sql = "SELECT 
            s.syssno,
            s.sysrno,
            s.sysrtp,
            s.sysdes1,
            s.sysdes2,
            s.sysadusr,
            DATE_FORMAT(s.sysaddt, '%d-%m-%Y') as formatted_date
        FROM sysmast s" . $whereClause . " ORDER BY s.sysrno";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/view.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>System Master Data</title>
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
        .btn-add {
            background-color: #2196F3;
            margin-bottom: 20px;
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
    <div class="container">
        <h1>System Master Data</h1>

        <div class="search-container">
            <form method="GET" action="">
                <input type="text" id="systemSearch" name="search" placeholder="Search by Description, Type or Ref No..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
            
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Ref No</th>
                        <th>System Type</th>
                        <th>Description 1</th>
                        <th>Description 2</th>
                        <th>Added By</th>
                        <th>Added Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['sysrno']); ?></td>
                        <td><?php echo htmlspecialchars($row['sysrtp']); ?></td>
                        <td><?php echo htmlspecialchars($row['sysdes1']); ?></td>
                        <td><?php echo htmlspecialchars($row['sysdes2']); ?></td>
                        <td><?php echo htmlspecialchars($row['sysadusr']); ?></td>
                        <td><?php echo htmlspecialchars($row['formatted_date']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="../amend/sysamend.php?id=<?php echo $row['syssno']; ?>" class="btn btn-update">Update</a>
                                <a href="../delete/sysdelete.php?id=<?php echo $row['syssno']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this system record?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No records found in System Master table.</p>
        <?php endif; ?>
    </div>

    <!-- jQuery and jQuery UI for autocomplete -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    
    <script>
    $(function() {
        // Autocomplete for system search
        $("#systemSearch").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "search_system.php",
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