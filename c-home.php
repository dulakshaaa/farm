<?php
// Include database connection
include('./includes/connect.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./includes/login.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM usemast WHERE USRSNO = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: ./includes/login.php");
    exit();
}

$username = $_SESSION['username'];

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Filter parameters with sanitization
function sanitizeInput($conn, $input)
{
    return $conn->real_escape_string(trim($input));
}

$farm_filter = isset($_GET['farm']) ? sanitizeInput($conn, $_GET['farm']) : '';
$breed_filter = isset($_GET['breed']) ? sanitizeInput($conn, $_GET['breed']) : '';
$batch_filter = isset($_GET['batch']) ? sanitizeInput($conn, $_GET['batch']) : '';
$officer_filter = isset($_GET['officer']) ? sanitizeInput($conn, $_GET['officer']) : '';
$date_from = isset($_GET['date_from']) ? sanitizeInput($conn, $_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitizeInput($conn, $_GET['date_to']) : '';
$search = isset($_GET['search']) ? sanitizeInput($conn, $_GET['search']) : '';
$sort_by = isset($_GET['sort_by']) ? sanitizeInput($conn, $_GET['sort_by']) : 'v.VISDDT';
$sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] == 'ASC' ? 'ASC' : 'DESC';

// Validate sort_by to prevent SQL injection
$allowed_sort_columns = [
    'v.VISDDT',
    'fa.FARNAME',
    'b.BATCODE',
    'f.FLONAME',
    'v.VISAGE',
    'b.BATCHICKS',
    'v.VISMORTALITY',
    'v.VISBLNBIRD',
    'v.VISFEEDCONSUMED',
    'v.VISFEEDBAL',
    'v.VISAVGWGT',
    'v.VISFCR',
    'br.BRDNAME'
];
if (!in_array($sort_by, $allowed_sort_columns)) {
    $sort_by = 'v.VISDDT';
}

// Build the WHERE clause dynamically
$where_clauses = ["f.floname = '$username'"]; // Only show visits for current user
if ($farm_filter) $where_clauses[] = "v.VISFARSNO = '$farm_filter'";
if ($breed_filter) $where_clauses[] = "b.BATBREEDSNO = '$breed_filter'";
if ($batch_filter) $where_clauses[] = "v.VITBATSNO = '$batch_filter'";
if ($officer_filter) $where_clauses[] = "v.VISFIELDOFF = '$officer_filter'";
if ($date_from) $where_clauses[] = "v.VISDDT >= '$date_from'";
if ($date_to) $where_clauses[] = "v.VISDDT <= '$date_to'";
if ($search) {
    $where_clauses[] = "(b.BATCODE LIKE '%$search%' OR 
                        fa.FARNAME LIKE '%$search%' OR 
                        f.FLONAME LIKE '%$search%' OR
                        br.BRDNAME LIKE '%$search%')";
}
$where = implode(" AND ", $where_clauses);

// Query for total records (for pagination)
$count_sql = "SELECT COUNT(*) as total FROM visitmast v
              LEFT JOIN flomast f ON v.VISFIELDOFF = f.FLOSNO
              LEFT JOIN farma fa ON v.VISFARSNO = fa.FARSNO
              LEFT JOIN batmast b ON v.VITBATSNO = b.BATSNO
              LEFT JOIN breedmast br ON b.BATBREEDSNO = br.BRDSNO
              WHERE $where";
$count_result = mysqli_query($conn, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Main query with filters, sorting, and pagination
$sql = "SELECT 
            v.*,
            b.BATCHICKS AS initial_birds,
            b.BATDDT AS batch_date,
            f.FLONAME AS field_officer_name,
            fa.FARNAME AS farm_name,
            b.BATCODE AS batch_CODE,
            u.USRNAME AS user_name,
            br.BRDNAME AS breed_name,
            a.AREANAME AS area_name,
            

            DATEDIFF(v.VISDDT, b.BATDDT) AS age_days
        FROM visitmast v
        LEFT JOIN flomast f ON v.VISFIELDOFF = f.FLOSNO
        LEFT JOIN farma fa ON v.VISFARSNO = fa.FARSNO
        LEFT JOIN batmast b ON v.VITBATSNO = b.BATSNO
        LEFT JOIN breedmast br ON b.BATBREEDSNO = br.BRDSNO
        LEFT JOIN usemast u ON v.VISUSRSNO = u.USRSNO
        LEFT JOIN areamast a ON a.AREASNO = fa.FARAREASNO
        WHERE $where
        ORDER BY $sort_by $sort_order
        LIMIT $offset, $records_per_page";

$result = mysqli_query($conn, $sql);
$visits = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Fetch filter options
$farm_options = mysqli_query($conn, "SELECT FARSNO, FARNAME FROM farma ORDER BY FARNAME");
$breed_options = mysqli_query($conn, "SELECT BRDSNO, BRDNAME FROM breedmast ORDER BY BRDNAME");
$batch_options = mysqli_query($conn, "SELECT BATSNO, BATCODE FROM batmast ORDER BY BATCODE");
$officer_options = mysqli_query($conn, "SELECT FLOSNO, FLONAME FROM flomast ORDER BY FLONAME");

// Export to CSV
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="visits_report.csv"');
    $output = fopen('php://output', 'w');

    // Header row
    fputcsv($output, [
        'Visit Date',
        'Farmer Name',
        'Batch Code',
        'Field Officer',
        'Batch Start Date',
        'Age (Days)',
        'Initial Birds',
        'Mortality',
        'Bird Balance',
        'Feed Consumed',
        'Feed Balance',
        'Avg Weight',
        'area name',
        'FCR',
        'Breed Name'
    ]);

    // Data rows
    $export_sql = str_replace("LIMIT $offset, $records_per_page", "", $sql);
    $export_result = mysqli_query($conn, $export_sql);
    while ($row = mysqli_fetch_assoc($export_result)) {
        fputcsv($output, [
            $row['VISDDT'],
            $row['farm_name'],
            $row['batch_CODE'],
            $row['field_officer_name'],
            $row['batch_date'],
            $row['age_days'],
            $row['initial_birds'],
            $row['VISMORTALITY'],
            $row['VISBLNBIRD'],
            $row['VISFEEDCONSUMED'],
            $row['VISFEEDBAL'],
            $row['VISAVGWGT'],
            $row['area_name'],
            $row['VISFCR'],
            $row['breed_name']
        ]);
    }
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriPro | Visit Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/home2.css">
    <link rel="stylesheet" href="./css/table.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>

    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --accent: #ff5722;
            --accent-light: #ff7043;
            --dark-1: #0f172a;
            --dark-2: #1e293b;
            --dark-3: #334155;
            --light-1: #f8fafc;
            --light-2: #e2e8f0;
            --light-3: #94a3b8;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --radius-sm: 0.25rem;
            --radius: 0.5rem;
        }

        .stat-card {
            background-color: var(--dark-2);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border-left: 4px solid var(--accent);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-left-color: var(--accent-light);
            background-color: var(--dark-3);
        }

        .stat-card h3 {
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
            color: var(--accent);
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .stat-card p {
            font-size: 0.9375rem;
            margin: 0.5rem 0;
            color: var(--light-2);
        }

        .stat-card p strong {
            color: var(--accent-light);
            font-weight: 500;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .visit-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Inter', sans-serif;
            margin-top: 1.5rem;
            background-color: var(--dark-2);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .visit-table th,
        .visit-table td {
            font-size: 10px;
            padding: 3px 6px;
            border: 1px solid var(--dark-3);
            text-align: left;
            color: var(--light-2);
        }

        .visit-table th {
            background-color: var(--dark-3);
            color: var(--light-1);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .visit-table tbody tr {
            transition: var(--transition);
        }

        .visit-table tbody tr:hover {
            background-color: var(--dark-3);
        }

        .visit-table tbody tr:nth-child(even) {
            background-color: rgba(255, 87, 34, 0.05);
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
            background: var(--dark-3);
            padding: 1.5rem;
            border-radius: var(--radius-sm);
        }

        .filter-form input,
        .filter-form select {
            padding: 0.75rem;
            border: 1px solid var(--light-3);
            border-radius: var(--radius-sm);
            background: var(--dark-2);
            color: var(--light-1);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .filter-form input:focus,
        .filter-form select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        .filter-form button {
            padding: 0.75rem 1.5rem;
            background-color: var(--secondary);
            color: var(--light-1);
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-form button:hover {
            background-color: var(--secondary-dark);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .visit-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }

        .status-active {
            color: var(--success);
            font-weight: 600;
        }

        .status-inactive {
            color: var(--danger);
            font-weight: 600;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            color: var(--primary);
            text-decoration: none;
            border: 1px solid var(--primary-dark);
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .pagination a:hover {
            background-color: var(--primary-dark);
            color: var(--light-1);
        }

        .pagination a.active {
            background-color: var(--primary);
            color: var(--light-1);
            border-color: var(--primary);
        }

        p {
            color: var(--light-2);
            text-align: center;
            padding: 1rem;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--dark-2);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            font-size: 0.7rem;
            /* smaller text */
        }

        thead th {
            background-color: var(--dark-3);
            color: var(--light-1);
            font-weight: 200;
            padding: 0.2rem 0.4rem;
            /* reduced padding */
            text-align: left;
            border-bottom: 2px solid var(--primary-dark);
            position: sticky;
            top: 0;
            z-index: 1;
        }

        thead th a {
            color: var(--light-1);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.01rem;
            /* smaller gap */
        }

        thead th a:hover {
            color: var(--primary-light);
        }

        tbody td {
            padding: 0.4rem 0.2rem;
            /* reduced padding */
            color: var(--light-2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        tbody tr {
            transition: var(--transition);
        }

        tbody tr:hover {
            background-color: var(--dark-3);
            transform: translateY(-1px);
            /* subtle hover lift */
        }


        .status-active {
            color: var(--success);
            font-weight: 600;
        }

        .status-inactive {
            color: var(--danger);
            font-weight: 600;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            color: var(--primary);
            text-decoration: none;
            border: 1px solid var(--primary-dark);
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .pagination a:hover {
            background-color: var(--primary-dark);
            color: var(--light-1);
        }

        .pagination a.active {
            background-color: var(--primary);
            color: var(--light-1);
            border-color: var(--primary);
        }

        p {
            color: var(--light-2);
            text-align: center;
            padding: 1rem;
        }

        @media (max-width: 1024px) {
            .filter-form {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .filter-form {
                grid-template-columns: 1fr;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .actions {
                flex-direction: column;
                align-items: flex-end;
            }
        }

        @media (max-width: 480px) {
            table {
                display: flex;
                flex-direction: column;
            }

            thead {
                display: none;
            }

            tbody,
            tr,
            td {
                display: block;
                width: 100%;
            }

            tbody tr {
                margin-bottom: 1rem;
                padding: 0.5rem;
                background: var(--dark-3);
                border-radius: var(--radius-sm);
            }

            tbody td {
                padding: 0px;
                border-bottom: none;
                position: relative;
                padding-left: 50%;
            }

            tbody td:before {
                content: attr(data-label);
                position: absolute;
                left: 0.5rem;
                width: 45%;
                padding-right: 0.5rem;
                font-weight: 600;
                color: var(--light-1);
                white-space: nowrap;
            }
        }
    </style>

</head>


<body>

    <div class="app-container">

        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">FV</div>
                <div class="app-name">Field Visit</div>
            </div>
            <nav class="nav-menu">
                <!-- Existing Options -->
                <a href="#" class="nav-item active">
                    <div class="nav-icon"><i class="fas fa-tachometer-alt"></i></div>
                    <div class="nav-label">Dashboard</div>
                </a>

                <a href="./add/c-farmeradd.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-tractor"></i></div>
                    <div class="nav-label">Farmers</div>
                    <!-- Image Suggestion: Aerial view of poultry farm -->
                </a>





                <!-- Settings at bottom -->
                <a href="#" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-cog"></i></div>
                    <div class="nav-label">Settings</div>
                </a>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Top Navigation Bar -->
            <header class="top-nav">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search farms, batches, reports...">
                </div>
                <!-- Modify your user-menu div in the top-nav to include a logout option -->
                <!-- Replace your existing user-menu div with this -->
                <div class="user-menu">
                    <div class="notification-bell">
                        <i class="fas fa-bell"></i>
                        <div class="notification-badge">3</div>
                    </div>
                    <div class="user-profile">
                        <div class="user-avatar"><?php echo strtoupper(substr($user['USRNAME'], 0, 2)); ?></div>
                        <div class="user-details">
                            <div class="user-name"><?php echo htmlspecialchars($user['USRNAME']); ?></div>
                            <div class="user-role"><?php echo htmlspecialchars($user['USRCODE']); ?></div>
                        </div>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>

                        <div class="user-dropdown">
                            <a href="./profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                            <a href="./includes/register.php" class="dropdown-item">
                                <i class="fas fa-cog"></i> Register User
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="./includes/logout.php" class="dropdown-item logout-item">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <div class="dashboard-header">
                    <h1 class="dashboard-title">User Visit Dashboard</h1>
                    <div class="dashboard-actions">
                        <button class="btn btn-primary" onclick="window.location.href='./add/c-visitadd.php'">
                            <i class="fas fa-plus"></i> Add visit
                        </button>
                        <button class="btn btn-secondary" onclick="window.location.href='?<?php echo http_build_query(array_merge($_GET, ['export' => 'csv'])); ?>'">
                            <i class="fas fa-download"></i> Export CSV
                        </button>

                        <button class="btn btn-secondary" id="export-pdf">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </button>
                    </div>
                </div>

                <!-- Filter Form -->
                <form class="filter-form" method="GET" id="filter-form">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search visits...">

                    <select name="farm">
                        <option value="">All Farms</option>
                        <?php while ($farm = mysqli_fetch_assoc($farm_options)): ?>
                            <option value="<?php echo $farm['FARSNO']; ?>" <?php echo $farm_filter == $farm['FARSNO'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($farm['FARNAME']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <select name="breed">
                        <option value="">All Breeds</option>
                        <?php while ($breed = mysqli_fetch_assoc($breed_options)): ?>
                            <option value="<?php echo $breed['BRDSNO']; ?>" <?php echo $breed_filter == $breed['BRDSNO'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($breed['BRDNAME']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <select name="batch">
                        <option value="">All Batches</option>
                        <?php while ($batch = mysqli_fetch_assoc($batch_options)): ?>
                            <option value="<?php echo $batch['BATSNO']; ?>" <?php echo $batch_filter == $batch['BATSNO'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($batch['BATCODE']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <select name="officer">
                        <option value="">All Officers</option>
                        <?php while ($officer = mysqli_fetch_assoc($officer_options)): ?>
                            <option value="<?php echo $officer['FLOSNO']; ?>" <?php echo $officer_filter == $officer['FLOSNO'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($officer['FLONAME']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" placeholder="From Date">
                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" placeholder="To Date">

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='?'">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </form>

                <!-- Visit Table -->
                <?php if ($visits): ?>
                    <div class="table-responsive">
                        <table class="visit-table">
                            <thead>
                                <tr>
                                    <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'v.VISDDT', 'sort_order' => $sort_by == 'v.VISDDT' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Visit Date <?php echo $sort_by == 'v.VISDDT' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a></th>
                                    <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'b.BATCODE', 'sort_order' => $sort_by == 'b.BATCODE' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Batch <?php echo $sort_by == 'b.BATCODE' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a></th>
                                        <th>Breed</th>
                                    <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'fa.FARNAME', 'sort_order' => $sort_by == 'fa.FARNAME' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Farmer <?php echo $sort_by == 'fa.FARNAME' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a></th>
                                    <th>Area</th>

                                    <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'f.FLONAME', 'sort_order' => $sort_by == 'f.FLONAME' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Officer <?php echo $sort_by == 'f.FLONAME' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a></th>
                                    <th>Batch Date</th>
                                    <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'v.VISAGE', 'sort_order' => $sort_by == 'v.VISAGE' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Age <?php echo $sort_by == 'v.VISAGE' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a></th>
                                    <th>Initial Birds</th>
                                    <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'v.VISMORTALITY', 'sort_order' => $sort_by == 'v.VISMORTALITY' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Mortality <?php echo $sort_by == 'v.VISMORTALITY' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a></th>
                                    <th>Motality %</th>


                                    <th>Bird Balance</th>
                                    <th>Feed Consumed</th>
                                    <th>Feed Balance</th>
                                    <th>Avg feed</th>
                                    <th>Avg Weight</th>
                                    <th>FCR</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($visits as $visit): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($visit['VISDDT']) ?></td>
                                        <td><?= htmlspecialchars($visit['batch_CODE']) ?></td>
                                        <td><?= htmlspecialchars($visit['breed_name']) ?></td>
                                        <td><?= htmlspecialchars($visit['farm_name']) ?></td>
                                        <td><?= htmlspecialchars($visit['area_name']) ?></td>
                                        <td><?= htmlspecialchars($visit['field_officer_name']) ?></td>
                                        <td><?= htmlspecialchars($visit['batch_date']) ?></td>
                                        <td><?= htmlspecialchars($visit['age_days']) ?></td>
                                        <td><?= number_format($visit['initial_birds']) ?></td>
                                        <td><?= number_format($visit['VISMORTALITY']) ?></td>
                                        <td><?= number_format($visit['VISMOTPCN']) ?></td>
                                        

                                        <td><?= number_format($visit['VISBLNBIRD']) ?></td>
                                        <td><?= number_format($visit['VISFEEDCONSUMED'], 2) ?></td>
                                        <td><?= number_format($visit['VISFEEDBAL'], 2) ?></td>
                                        <td><?= number_format($visit['VISAVGFEED'], 2) ?></td>
                                        <td><?= number_format($visit['VISAVGWGT'], 2) ?></td>
                                        <td><?= number_format($visit['VISFCR'], 2) ?></td>
                                       
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">« Prev</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next »</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p>No visits found matching the criteria.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        $(document).ready(function() {
            // PDF Export
            $('#export-pdf').on('click', function() {
                const {
                    jsPDF
                } = window.jspdf;
                const doc = new jsPDF({
                    orientation: 'landscape',
                    unit: 'pt',
                    format: 'a4'
                });

                // Add title
                doc.setFontSize(16);
                doc.text('Visit Report - <?php echo date("Y-m-d"); ?>', 40, 40);

                // Add table
                doc.autoTable({
                    html: '.visit-table',
                    startY: 60,
                    theme: 'grid',
                    headStyles: {
                        fillColor: [33, 150, 243],
                        textColor: [255, 255, 255],
                        fontSize: 10,
                        fontStyle: 'bold',
                        halign: 'center'
                    },
                    bodyStyles: {
                        textColor: [33, 33, 33],
                        fontSize: 9,
                        halign: 'center'
                    },
                    alternateRowStyles: {
                        fillColor: [240, 240, 240]
                    },
                    margin: {
                        top: 60,
                        left: 40,
                        bottom: 40
                    }
                });

                // Save the PDF
                doc.save('visit_report.pdf');
            });

            // Live search with AJAX
            $('input[name="search"]').on('keyup', function() {
                const formData = $('#filter-form').serialize();
                $.ajax({
                    url: window.location.pathname,
                    method: 'GET',
                    data: formData,
                    success: function(response) {
                        const newTable = $(response).find('.visit-table tbody').html();
                        const newPagination = $(response).find('.pagination').html();
                        $('.visit-table tbody').html(newTable);
                        $('.pagination').html(newPagination);
                    },
                    error: function() {
                        alert('Error fetching search results.');
                    }
                });
            });
        });
    </script>
</body>

</html>