<?php
require_once '../includes/connect.php';
require_login();  // This will redirect to login if not authenticated

// 2. Get current user data if needed
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM usemast WHERE USRSNO = $user_id");
$current_user = $user_query->fetch_assoc();
include '../includes/batchnavbar.php';


// Initialize search variable
$search = '';
$whereClause = '';

// Check if search parameter is set
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $whereClause = " WHERE b.BATCODE LIKE '%$search%'";
}

// Modified query to join with BREEDMAST and FARMAST tables
$sql = "SELECT 
            b.BATCODE,
            f.FARNAME,
            b.BATDDT,
            br.BRDNAME,
            b.BATCHICKS,
            b.BATACTFLG,
            b.BATFARSNO,
            b.BATBREEDSNO
        FROM BATMAST b
        LEFT JOIN FARMA f ON b.BATFARSNO = f.FARSNO
        LEFT JOIN BREEDMAST br ON b.BATBREEDSNO = br.BRDSNO" . $whereClause;
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
    <title>BATMAST Data View</title>
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
        <h1>Batches Data</h1>

        <div class="search-container">
            <form method="GET" action="">
                <input type="text" id="batchSearch" name="search" placeholder="Search by Batch Code..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Batch Code</th>
                        <th>Farm Name</th>
                        <th>Batch Date</th>
                        <th>Breed Name</th>
                        <th>Chicks</th>
                        <th>Active Flag</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['BATCODE']); ?></td>
                        <td><?php echo htmlspecialchars($row['FARNAME']); ?></td>
                        <td><?php echo htmlspecialchars($row['BATDDT']); ?></td>
                        <td><?php echo htmlspecialchars($row['BRDNAME']); ?></td>
                        <td><?php echo htmlspecialchars($row['BATCHICKS']); ?></td>
                        <td class="<?php echo $row['BATACTFLG'] == 1 ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $row['BATACTFLG'] == 1 ? 'Active' : 'Inactive'; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="../amend/batchamend.php?batcode=<?php echo $row['BATCODE']; ?>" class="btn btn-update">Update</a>
                                </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No records found in BATMAST table.</p>
        <?php endif; ?>
    </div>

    <!-- jQuery and jQuery UI for autocomplete -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    
    <script>
    $(function() {
        // Autocomplete for batch codes
        $("#batchSearch").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "search_batches.php",
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