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
    $whereClause = " WHERE b.batcode LIKE '%$search%' OR f.FLONAME LIKE '%$search%'";
}

// Fetch visit records with search
$visits = [];
$visitQuery = "SELECT v.*, b.BATCODE, b.BATDDT, f.FLONAME 
               FROM visitmast v 
               JOIN batmast b ON v.VITBATSNO = b.BATSNO
               JOIN flomast f ON v.VISFIELDOFF = f.FLOSNO"
               . $whereClause .
               " ORDER BY v.VISDDT DESC";
$visitResult = $conn->query($visitQuery);
if ($visitResult) {
    while ($row = $visitResult->fetch_assoc()) {
        $visits[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit Records</title>
    <link rel="stylesheet" href="../css/visit.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
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
        <h1>Visit Records</h1>
        
        <div class="search-container">
            <form method="GET" action="">
                <input type="text" id="visitSearch" name="search" placeholder="Search by Batch Code or Officer..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        
        <?php if ($visitResult && $visitResult->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                    <th>VISSNO</th>
                        <th>Batch Code</th>
                        <th>Visit Date</th>
                        <th>Field Officer</th>
                        <th>Mortality</th>
                        <th>Mortality %</th>
                        <th>Balance Birds</th>
                        <th>Avg Weight (kg)</th>
                        <th>Avg Feed (kg)</th>
                        <th>FCR</th>
                        <th>Feed Balance (kg)</th>
                        <th>Age</th>
                        
                      
                        <th>Input Feed Bag</th>
                        <th>Feed Consumed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($visits as $visit): ?>
                        <tr>
                        <td><?php echo htmlspecialchars($visit['VISSNO']); ?></td>
                            <td><?php echo htmlspecialchars($visit['BATCODE']); ?></td>
                            <td><?php echo htmlspecialchars($visit['VISDDT']); ?></td>
                            <td><?php echo htmlspecialchars($visit['FLONAME']); ?></td>
                            <td><?php echo htmlspecialchars($visit['VISMORTALITY']); ?></td>
                            <td><?php echo htmlspecialchars($visit['VISMOTPCN']); ?></td>
                            <td><?php echo htmlspecialchars($visit['VISBLNBIRD']); ?></td>
                            <td><?php echo htmlspecialchars($visit['VISAVGWGT']); ?></td>
                            <td><?php echo htmlspecialchars($visit['VISAVGFEED']); ?></td>
                            <td><?php echo htmlspecialchars($visit['VISFCR']); ?></td>
                            <td><?php echo htmlspecialchars($visit['VISFEEDBAL']); ?></td>
                            <td><?php echo htmlspecialchars($visit['VISAGE']); ?></td>
                           
                           
                           
                            <td><?php echo htmlspecialchars($visit['VISINPFEEDBAG']); ?></td>
                            <td><?php echo htmlspecialchars($visit['VISFEEDCONSUMED']); ?></td>
                            <td class="action-buttons">
                                <a href="editvisit.php?id=<?php echo $visit['BATCODE']; ?>" class="btn btn-update">Edit</a>
                                <a href="deletevisit.php?id=<?php echo $visit['BATCODE']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No visit records found.</p>
        <?php endif; ?>
    </div>
    
    <script>
    $(function() {
        $("#visitSearch").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "search_visit.php",
                    dataType: "json",
                    data: { term: request.term },
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
</body>
</html>
