<?php
require_once '../includes/connect.php';
require_login(); // Redirects to login if not authenticated
include '../includes/visitnavbar.php';
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
    'v.VISSNO',
    'v.VISDDT',
    'fa.FARNAME',
    'b.BATCODE',
    'f.FLONAME',
    'v.VISAGE',
    'b.BATCHICKS',
    'v.VISMORTALITY',
    'v.VISMOTPCN',
    'v.VISBLNBIRD',
    'v.VISFEEDCONSUMED',
    'v.VISFEEDBAL',
    'v.VISAVGWGT',
    'v.VISFCR',
    'br.BRDNAME',
    'a.AREANAME',
    'v.VISINPFEEDBAG',
    'v.VISAVGFEED',
    'b.BATDDT'
];
if (!in_array($sort_by, $allowed_sort_columns)) {
    $sort_by = 'v.VISDDT';
}

// Build the WHERE clause dynamically
$where_clauses = [];
if ($farm_filter) $where_clauses[] = "v.VISFARSNO = '$farm_filter'";
if ($breed_filter) $where_clauses[] = "b.BATBREEDSNO = '$breed_filter'";
if ($batch_filter) $where_clauses[] = "v.VITBATSNO = '$batch_filter'";
if ($officer_filter) $where_clauses[] = "v.VISFIELDOFF = '$officer_filter'";
if ($date_from) $where_clauses[] = "v.VISDDT >= '$date_from'";
if ($date_to) $where_clauses[] = "v.VISDDT <= '$date_to'";
if ($search) {
    $where_clauses[] = "(b.BATCODE LIKE '%$search%' OR fa.FARNAME LIKE '%$search%' OR f.FLONAME LIKE '%$search%' OR br.BRDNAME LIKE '%$search%')";
}
$where = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";

// Query for total records (for pagination)
$count_sql = "SELECT COUNT(*) as total FROM visitmast v
              LEFT JOIN flomast f ON v.VISFIELDOFF = f.FLOSNO
              LEFT JOIN farma fa ON v.VISFARSNO = fa.FARSNO
              LEFT JOIN batmast b ON v.VITBATSNO = b.BATSNO
              LEFT JOIN breedmast br ON b.BATBREEDSNO = br.BRDSNO
              LEFT JOIN areamast a ON a.AREASNO = fa.FARAREASNO
              $where";
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Main query with filters, sorting, and pagination
$sql = "SELECT 
            v.*, 
            b.BATCODE AS batch_CODE, 
            b.BATDDT AS batch_date, 
            b.BATCHICKS AS initial_birds, 
            f.FLONAME AS field_officer_name, 
            fa.FARNAME AS farm_name, 
            br.BRDNAME AS breed_name, 
            a.AREANAME AS area_name,
            DATEDIFF(v.VISDDT, b.BATDDT) AS age_days
        FROM visitmast v
        LEFT JOIN flomast f ON v.VISFIELDOFF = f.FLOSNO
        LEFT JOIN farma fa ON v.VISFARSNO = fa.FARSNO
        LEFT JOIN batmast b ON v.VITBATSNO = b.BATSNO
        LEFT JOIN breedmast br ON b.BATBREEDSNO = br.BRDSNO
        LEFT JOIN areamast a ON a.AREASNO = fa.FARAREASNO
        $where
        ORDER BY $sort_by $sort_order
        LIMIT $offset, $records_per_page";
$result = $conn->query($sql);
$visits = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Fetch filter options
$farm_options = $conn->query("SELECT FARSNO, FARNAME FROM farma ORDER BY FARNAME");
$breed_options = $conn->query("SELECT BRDSNO, BRDNAME FROM breedmast ORDER BY BRDNAME");
$batch_options = $conn->query("SELECT BATSNO, BATCODE FROM batmast ORDER BY BATCODE");
$officer_options = $conn->query("SELECT FLOSNO, FLONAME FROM flomast ORDER BY FLONAME");

// Export to CSV
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="visits_report.csv"');
    $output = fopen('php://output', 'w');

    // Header row
    fputcsv($output, [
        'VISSNO',
        'Visit Date',
        'Farmer Name',
        'Batch Code',
        'Field Officer',
        'Batch Start Date',
        'Age (Days)',
        'Initial Birds',
        'Mortality',
        'Mortality %',
        'Bird Balance',
        'Feed Consumed',
        'Feed Balance',
        'Avg Weight',
        'FCR',
        'Breed Name',
        'Area Name',
        'Input Feed Bag',
        'Avg Feed'
    ]);

    // Data rows
    $export_sql = str_replace("LIMIT $offset, $records_per_page", "", $sql);
    $export_result = $conn->query($export_sql);
    while ($row = $export_result->fetch_assoc()) {
        fputcsv($output, [
            $row['VISSNO'],
            $row['VISDDT'],
            $row['farm_name'],
            $row['batch_CODE'],
            $row['field_officer_name'],
            $row['batch_date'],
            $row['age_days'],
            $row['initial_birds'],
            $row['VISMORTALITY'],
            $row['VISMOTPCN'],
            $row['VISBLNBIRD'],
            $row['VISFEEDCONSUMED'],
            $row['VISFEEDBAL'],
            $row['VISAVGWGT'],
            $row['VISFCR'],
            $row['breed_name'],
            $row['area_name'],
            $row['VISINPFEEDBAG'],
            $row['VISAVGFEED']
        ]);
    }
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit Records</title>
    <link rel="stylesheet" href="../css/visit1.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
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
    </style>
</head>

<body>
    <div class="container">
        <h1>Visit Records</h1>

        <!-- Filter Form -->
        <form class="filter-form" method="GET">
            <input type="text" id="visitSearch" name="search" placeholder="Search by Batch, Farmer, Officer..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="farm">
                <option value="">All Farms</option>
                <?php while ($farm = $farm_options->fetch_assoc()): ?>
                    <option value="<?php echo $farm['FARSNO']; ?>" <?php echo $farm_filter == $farm['FARSNO'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($farm['FARNAME']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <select name="breed">
                <option value="">All Breeds</option>
                <?php while ($breed = $breed_options->fetch_assoc()): ?>
                    <option value="<?php echo $breed['BRDSNO']; ?>" <?php echo $breed_filter == $breed['BRDSNO'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($breed['BRDNAME']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <select name="batch">
                <option value="">All Batches</option>
                <?php while ($batch = $batch_options->fetch_assoc()): ?>
                    <option value="<?php echo $batch['BATSNO']; ?>" <?php echo $batch_filter == $batch['BATSNO'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($batch['BATCODE']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <select name="officer">
                <option value="">All Officers</option>
                <?php while ($officer = $officer_options->fetch_assoc()): ?>
                    <option value="<?php echo $officer['FLOSNO']; ?>" <?php echo $officer_filter == $officer['FLOSNO'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($officer['FLONAME']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" placeholder="From Date">
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" placeholder="To Date">
            <button type="submit">Filter</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='?'">
                <i class="fas fa-times"></i> Clear
            </button>
            <button type="button" class="btn btn-export" onclick="window.location.href='?<?php echo http_build_query(array_merge($_GET, ['export' => 'csv'])); ?>'">Export CSV</button>
            <button type="button" class="btn btn-export" id="export-pdf">Export PDF</button>
        </form>

        <?php if (!empty($visits)): ?>
            <table>
                <thead>
                    <tr>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'v.VISSNO', 'sort_order' => $sort_by == 'v.VISSNO' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                VISSNO <?php echo $sort_by == 'v.VISSNO' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'v.VISDDT', 'sort_order' => $sort_by == 'v.VISDDT' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Visit Date <?php echo $sort_by == 'v.VISDDT' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'fa.FARNAME', 'sort_order' => $sort_by == 'fa.FARNAME' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Farmer <?php echo $sort_by == 'fa.FARNAME' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'b.BATCODE', 'sort_order' => $sort_by == 'b.BATCODE' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Batch Code <?php echo $sort_by == 'b.BATCODE' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'br.BRDNAME', 'sort_order' => $sort_by == 'br.BRDNAME' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Breed <?php echo $sort_by == 'br.BRDNAME' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'f.FLONAME', 'sort_order' => $sort_by == 'f.FLONAME' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Field Officer <?php echo $sort_by == 'f.FLONAME' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'b.BATDDT', 'sort_order' => $sort_by == 'b.BATDDT' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Batch Date <?php echo $sort_by == 'b.BATDDT' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'v.VISAGE', 'sort_order' => $sort_by == 'v.VISAGE' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Age <?php echo $sort_by == 'v.VISAGE' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'b.BATCHICKS', 'sort_order' => $sort_by == 'b.BATCHICKS' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Initial Birds <?php echo $sort_by == 'b.BATCHICKS' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'v.VISMORTALITY', 'sort_order' => $sort_by == 'v.VISMORTALITY' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Mortality <?php echo $sort_by == 'v.VISMORTALITY' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'v.VISMOTPCN', 'sort_order' => $sort_by == 'v.VISMOTPCN' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Mortality % <?php echo $sort_by == 'v.VISMOTPCN' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'v.VISBLNBIRD', 'sort_order' => $sort_by == 'v.VISBLNBIRD' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Bird Balance <?php echo $sort_by == 'v.VISBLNBIRD' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'v.VISFEEDCONSUMED', 'sort_order' => $sort_by == 'v.VISFEEDCONSUMED' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Feed Consumed <?php echo $sort_by == 'v.VISFEEDCONSUMED' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'v.VISFEEDBAL', 'sort_order' => $sort_by == 'v.VISFEEDBAL' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Feed Balance <?php echo $sort_by == 'v.VISFEEDBAL' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'v.VISAVGWGT', 'sort_order' => $sort_by == 'v.VISAVGWGT' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Avg Weight <?php echo $sort_by == 'v.VISAVGWGT' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'v.VISFCR', 'sort_order' => $sort_by == 'v.VISFCR' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                FCR <?php echo $sort_by == 'v.VISFCR' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'a.AREANAME', 'sort_order' => $sort_by == 'a.AREANAME' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Area <?php echo $sort_by == 'a.AREANAME' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'v.VISINPFEEDBAG', 'sort_order' => $sort_by == 'v.VISINPFEEDBAG' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Input Feed Bag <?php echo $sort_by == 'v.VISINPFEEDBAG' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'v.VISAVGFEED', 'sort_order' => $sort_by == 'v.VISAVGFEED' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                Avg Feed <?php echo $sort_by == 'v.VISAVGFEED' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($visits as $visit): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($visit['VISSNO']); ?></td>
                            <td><?php echo htmlspecialchars($visit['VISDDT']); ?></td>
                            <td><?php echo htmlspecialchars($visit['farm_name']); ?></td>
                            <td><?php echo htmlspecialchars($visit['batch_CODE']); ?></td>
                            <td><?php echo htmlspecialchars($visit['breed_name']); ?></td>
                            <td><?php echo htmlspecialchars($visit['field_officer_name']); ?></td>
                            <td><?php echo htmlspecialchars($visit['batch_date']); ?></td>
                            <td><?php echo htmlspecialchars($visit['age_days']); ?></td>
                            <td><?php echo number_format($visit['initial_birds']); ?></td>
                            <td><?php echo number_format($visit['VISMORTALITY']); ?></td>
                            <td><?php echo number_format($visit['VISMOTPCN']); ?></td>
                            <td><?php echo number_format($visit['VISBLNBIRD']); ?></td>
                            <td><?php echo number_format($visit['VISFEEDCONSUMED'], 2); ?></td>
                            <td><?php echo number_format($visit['VISFEEDBAL'], 2); ?></td>
                            <td><?php echo number_format($visit['VISAVGWGT'], 2); ?></td>
                            <td><?php echo number_format($visit['VISFCR'], 2); ?></td>
                            <td><?php echo htmlspecialchars($visit['area_name']); ?></td>
                            <td><?php echo htmlspecialchars($visit['VISINPFEEDBAG']); ?></td>
                            <td><?php echo number_format($visit['VISAVGFEED'], 2); ?></td>
                            <td class="action-buttons">
                                <a href="editvisit.php?id=<?php echo $visit['batch_CODE']; ?>" class="btn btn-update">Edit</a>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

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

                doc.setFontSize(16);
                doc.text('Visit Records - <?php echo date("Y-m-d"); ?>', 40, 40);

                doc.autoTable({
                    html: 'table',
                    startY: 60,
                    theme: 'grid',
                    headStyles: {
                        fillColor: [33, 150, 243],
                        textColor: [255, 255, 255],
                        fontSize: 10
                    },
                    bodyStyles: {
                        textColor: [33, 33, 33],
                        fontSize: 9
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

                doc.save('visit_records.pdf');
            });
        });
    </script>
</body>

</html>