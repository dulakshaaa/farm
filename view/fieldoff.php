<?php
require_once '../includes/connect.php';
require_login();  // This will redirect to login if not authenticated

// 2. Get current user data if needed
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM usemast WHERE USRSNO = $user_id");
$current_user = $user_query->fetch_assoc();
include '../includes/fieldoffnavbar.php';

// Initialize search variable
$search = '';
$whereClause = '';

// Check if search parameter is set
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $whereClause = " WHERE f.FLONAME LIKE '%$search%' OR f.FLOCODE LIKE '%$search%' OR a.AREANAME LIKE '%$search%'";
}

// Query to join with areamast table
$sql = "SELECT 
            f.FLOSNO,
            f.FLONAME,
            f.FLOCODE,
            f.FLOTEL,
            f.FLOAREASNO,
            a.AREANAME,
            f.FLOACTFLG
        FROM FLOMAST f
        LEFT JOIN areamast a ON f.FLOAREASNO = a.AREASNO" . $whereClause;
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
    <title>Field Officer Data View</title>
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
        <h1>Field Officer Data</h1>

        <div class="search-container">
            <form method="GET" action="">
                <input type="text" id="officerSearch" name="search" placeholder="Search by Name, Code or Area..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Officer Name</th>
                        <th>Officer Code</th>
                        <th>Phone</th>
                        <th>Assigned Area</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['FLOSNO']); ?></td>
                        <td><?php echo htmlspecialchars($row['FLONAME']); ?></td>
                        <td><?php echo htmlspecialchars($row['FLOCODE']); ?></td>
                        <td><?php echo htmlspecialchars($row['FLOTEL']); ?></td>
                        <td><?php echo htmlspecialchars($row['AREANAME'] ?? 'N/A'); ?></td>
                        <td class="<?php echo $row['FLOACTFLG'] == 1 ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $row['FLOACTFLG'] == 1 ? 'Active' : 'Inactive'; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="../amend/fieldoffamend.php?id=<?php echo $row['FLOSNO']; ?>" class="btn btn-update">Update</a>
                                 </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No records found in FLOMAST table.</p>
        <?php endif; ?>
    </div>

    <!-- jQuery and jQuery UI for autocomplete -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    
    <script>
    $(function() {
        // Autocomplete for field officers
        $("#officerSearch").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "search_fieldoff.php",
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