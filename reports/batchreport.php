<?php
require_once '../includes/connect.php';

// Start session


// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Filter parameters
$farm_filter = isset($_GET['farm']) ? $conn->real_escape_string($_GET['farm']) : '';
$breed_filter = isset($_GET['breed']) ? $conn->real_escape_string($_GET['breed']) : '';
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$date_from = isset($_GET['date_from']) ? $conn->real_escape_string($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? $conn->real_escape_string($_GET['date_to']) : '';
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sort_by = isset($_GET['sort_by']) ? $conn->real_escape_string($_GET['sort_by']) : 'BATDDT';
$sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] == 'ASC' ? 'ASC' : 'DESC';

// Build the WHERE clause dynamically
$where_clauses = [];
if ($farm_filter) $where_clauses[] = "f.FARSNO = '$farm_filter'";
if ($breed_filter) $where_clauses[] = "br.BRDSNO = '$breed_filter'";
if ($status_filter !== '') $where_clauses[] = "b.BATACTFLG = '$status_filter'";
if ($date_from) $where_clauses[] = "b.BATDDT >= '$date_from'";
if ($date_to) $where_clauses[] = "b.BATDDT <= '$date_to'";
if ($search) $where_clauses[] = "(b.BATCODE LIKE '%$search%' OR f.FARNAME LIKE '%$search%' OR br.BRDNAME LIKE '%$search%')";
$where = $where_clauses ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Query for total records (for pagination)
$count_sql = "SELECT COUNT(*) as total FROM batmast b LEFT JOIN farma f ON b.BATFARSNO = f.FARSNO LEFT JOIN breedmast br ON b.BATBREEDSNO = br.BRDSNO $where";
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Query for totals summary
$summary_sql = "SELECT SUM(b.BATCHICKS) as total_chicks FROM batmast b LEFT JOIN farma f ON b.BATFARSNO = f.FARSNO LEFT JOIN breedmast br ON b.BATBREEDSNO = br.BRDSNO $where";
$summary_result = $conn->query($summary_sql);
$summary = $summary_result->fetch_assoc();

// Main query with filters, sorting, and pagination
$sql = "
    SELECT 
        b.BATCODE, 
        f.FARNAME, 
        br.BRDNAME, 
        b.BATDDT, 
        b.BATCHICKS, 
        b.BATACTFLG
    FROM 
        batmast b
    LEFT JOIN 
        farma f ON b.BATFARSNO = f.FARSNO
    LEFT JOIN 
        breedmast br ON b.BATBREEDSNO = br.BRDSNO
    $where
    ORDER BY $sort_by $sort_order
    LIMIT $offset, $records_per_page
";
$result = $conn->query($sql);

// Fetch farms and breeds for filter dropdowns
$farm_options = $conn->query("SELECT FARSNO, FARNAME FROM farma ORDER BY FARNAME");
$breed_options = $conn->query("SELECT BRDSNO, BRDNAME FROM breedmast ORDER BY BRDNAME");

// Export to CSV
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="batch_report.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Batch Code', 'Farm Name', 'Breed Name', 'Date', 'Chicks', 'Status']);
    
    $export_sql = str_replace("LIMIT $offset, $records_per_page", "", $sql);
    $export_result = $conn->query($export_sql);
    while ($row = $export_result->fetch_assoc()) {
        fputcsv($output, [
            $row['BATCODE'],
            $row['FARNAME'] ?? 'N/A',
            $row['BRDNAME'] ?? 'N/A',
            $row['BATDDT'],
            $row['BATCHICKS'],
            $row['BATACTFLG'] ? 'Active' : 'Inactive'
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
    <title>Advanced Batch Report</title>
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
            padding: clamp(1rem, 3vw, 2rem);
            line-height: 1.5;
            min-height: 100vh;
        }

        .container {
            background-color: var(--dark-2);
            padding: clamp(1rem, 3vw, 2rem);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            animation: fadeIn 0.5s ease forwards;
        }

        h1 {
            text-align: center;
            color: var(--primary-light);
            margin-bottom: 1.5rem;
            font-size: clamp(1.5rem, 3vw, 2rem);
        }

        .user-info {
            text-align: right;
            color: var(--light-3);
            margin-bottom: 1rem;
            font-size: 0.9rem;
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

        .filter-form input, .filter-form select {
            padding: 0.75rem;
            border: 1px solid var(--light-3);
            border-radius: var(--radius-sm);
            background: var(--dark-2);
            color: var(--light-1);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .filter-form input:focus, .filter-form select:focus {
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

.actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-bottom: 1rem;
}

        .actions button {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
        }

        .actions .csv-btn {
            background-color: var(--accent);
            color: var(--dark-1);
        }

        .actions .csv-btn:hover {
            background-color: #d97706;
        }

        .actions .pdf-btn {
            background-color: var(--primary);
            color: var(--light-1);
        }

        .actions .pdf-btn:hover {
            background-color: var(--primary-dark);
        }

        .summary {
            background: var(--dark-3);
            padding: 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.5rem;
            color: var(--light-2);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--dark-2);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        thead th {
            background-color: var(--dark-3);
            color: var(--light-1);
            font-weight: 600;
            padding: 1rem;
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
            gap: 0.25rem;
        }

        thead th a:hover {
            color: var(--primary-light);
        }

        tbody td {
            padding: 1rem;
            color: var(--light-2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        tbody tr {
            transition: var(--transition);
        }

        tbody tr:hover {
            background-color: var(--dark-3);
            transform: translateY(-2px);
        }

        .status-active { color: var(--success); font-weight: 600; }
        .status-inactive { color: var(--danger); font-weight: 600; }

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
            tbody, tr, td {
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
                padding: 0.5rem;
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

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Batch Report</h1>
        <?php if ($current_user): ?>
            <div class="user-info">Logged in as: <?php echo htmlspecialchars($current_user); ?></div>
        <?php endif; ?>

        <!-- Filter Form -->
        <form class="filter-form" method="GET" id="filter-form">
            <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by Batch, Farm, or Breed">
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
            <select name="status">
                <option value="">All Statuses</option>
                <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
            </select>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            <button type="submit">Filter</button>
        </form>

        <!-- Actions -->
        <div class="actions">
            <button class="csv-btn" onclick="window.location.href='?<?php echo http_build_query(array_merge($_GET, ['export' => 'csv'])); ?>'">Export CSV</button>
            <button class="pdf-btn" id="export-pdf">Export PDF</button>
        </div>

        <!-- Summary -->
        <div class="summary">
            Total Chicks: <?php echo number_format($summary['total_chicks'] ?? 0); ?>
        </div>

        <!-- Report Table -->
        <?php if ($result && $result->num_rows > 0): ?>
            <table id="report-table">
                <thead>
                    <tr>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'BATCODE', 'sort_order' => $sort_by == 'BATCODE' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">Batch Code <?php echo $sort_by == 'BATCODE' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'FARNAME', 'sort_order' => $sort_by == 'FARNAME' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">Farm Name <?php echo $sort_by == 'FARNAME' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'BRDNAME', 'sort_order' => $sort_by == 'BRDNAME' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">Breed Name <?php echo $sort_by == 'BRDNAME' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'BATDDT', 'sort_order' => $sort_by == 'BATDDT' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">Date <?php echo $sort_by == 'BATDDT' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'BATCHICKS', 'sort_order' => $sort_by == 'BATCHICKS' && $sort_order == 'ASC' ? 'DESC' : 'ASC'])); ?>">Chicks <?php echo $sort_by == 'BATCHICKS' ? ($sort_order == 'ASC' ? '↑' : '↓') : ''; ?></a></th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td data-label="Batch Code"><?php echo htmlspecialchars($row['BATCODE']); ?></td>
                            <td data-label="Farm Name"><?php echo htmlspecialchars($row['FARNAME'] ?? 'N/A'); ?></td>
                            <td data-label="Breed Name"><?php echo htmlspecialchars($row['BRDNAME'] ?? 'N/A'); ?></td>
                            <td data-label="Date"><?php echo htmlspecialchars($row['BATDDT']); ?></td>
                            <td data-label="Chicks"><?php echo number_format($row['BATCHICKS']); ?></td>
                            <td data-label="Status" class="<?php echo $row['BATACTFLG'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $row['BATACTFLG'] ? 'Active' : 'Inactive'; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
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
            <p>No batch records found matching the criteria.</p>
        <?php endif; ?>

        <?php $conn->close(); ?>
    </div>

    <script>
        $(document).ready(function() {
            // Live search with AJAX
            $('#search').on('keyup', function() {
                const formData = $('#filter-form').serialize();
                $.ajax({
                    url: 'batch_report.php',
                    method: 'GET',
                    data: formData,
                    success: function(response) {
                        const newTable = $(response).find('#report-table tbody').html();
                        $('#report-table tbody').html(newTable);
                    },
                    error: function() {
                        alert('Error fetching search results.');
                    }
                });
            });

            // PDF Export
            $('#export-pdf').on('click', function() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({
        orientation: 'landscape',
        unit: 'pt',
        format: 'a4'
    });

    // Add title
    doc.setFontSize(16);
    doc.text('Batch Report', 40, 40);

    // Add table using autoTable with a new theme
    doc.autoTable({
        html: '#report-table',
        startY: 60,
        theme: 'grid', // Apply grid theme (a more minimalist approach)
        headStyles: {
            fillColor: [33, 150, 243], // Blue color for header
            textColor: [255, 255, 255], // White text in the header
            fontSize: 10,
            fontStyle: 'bold',
            halign: 'center' // Center-align the header text
        },
        bodyStyles: {
            textColor: [33, 33, 33], // Dark gray text
            fontSize: 9,
            halign: 'center', // Center-align the body text
            valign: 'middle' // Middle vertical alignment
        },
        alternateRowStyles: {
            fillColor: [240, 240, 240] // Light gray background for alternate rows
        },
        margin: { top: 60, left: 40, bottom: 40 }, // Set margins to make the table fit well
        columnStyles: {
            0: { cellWidth: 50 }, // Adjust width of the first column
            1: { cellWidth: 100 } // Adjust width of the second column
        }
    });

    // Add summary below the table
    const finalY = doc.lastAutoTable.finalY;
    doc.setFontSize(12);
    doc.text(`Total Chicks: <?php echo number_format($summary['total_chicks'] ?? 0); ?>`, 40, finalY + 20);

    // Save the PDF
    doc.save('batch_report.pdf');
});

        });
    </script>
</body>
</html>