<?php


// Include database connection
include('./includes/connect.php'); // Ensure this file contains the database connection
require_role('admin');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./includes/login.php"); // Redirect to login page if user is not logged in
    exit();
}

// Fetch user details from the database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM usemast WHERE USRSNO = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Check if user exists
if (!$user) {
    header("Location: ./includes/login.php"); // Redirect to login if user not found
    exit();
}



// Notification data
$notifications = 3; // This can be dynamic by fetching from the database or any other source
// Query to get the total number of farms
$query = "SELECT COUNT(*) AS total_farmers FROM farma";
$result = mysqli_query($conn, $query);
// Fetch the result
$data = mysqli_fetch_assoc($result);
// Get the total number of farms
$totalFarmers = $data['total_farmers'];





// Query to get the total number of active farmers
// Query to get the total number of active farmers
$queryActive = "SELECT COUNT(*) AS active_farmers FROM farma WHERE FARACTFLG = 1";
$resultActive = mysqli_query($conn, $queryActive);
$dataActive = mysqli_fetch_assoc($resultActive);
$activeFarmers = $dataActive['active_farmers'];

// Query to get the total number of inactive farmers
$queryInactive = "SELECT COUNT(*) AS inactive_farmers FROM farma WHERE FARACTFLG = 0";
$resultInactive = mysqli_query($conn, $queryInactive);
$dataInactive = mysqli_fetch_assoc($resultInactive);
$inactiveFarmers = $dataInactive['inactive_farmers'];
// Query to get the total number of batches
$queryTotalBatches = "SELECT COUNT(*) AS total_batches FROM batmast";
$resultTotalBatches = mysqli_query($conn, $queryTotalBatches);
$dataTotalBatches = mysqli_fetch_assoc($resultTotalBatches);
$totalBatches = $dataTotalBatches['total_batches'];

// Query to get the total number of active batches
$queryActiveBatches = "SELECT COUNT(*) AS active_batches FROM batmast WHERE BATACTFLG = 1";
$resultActiveBatches = mysqli_query($conn, $queryActiveBatches);
$dataActiveBatches = mysqli_fetch_assoc($resultActiveBatches);
$activeBatches = $dataActiveBatches['active_batches'];

// Query to get the total number of inactive batches
$queryInactiveBatches = "SELECT COUNT(*) AS inactive_batches FROM batmast WHERE BATACTFLG = 0";
$resultInactiveBatches = mysqli_query($conn, $queryInactiveBatches);
$dataInactiveBatches = mysqli_fetch_assoc($resultInactiveBatches);
$inactiveBatches = $dataInactiveBatches['inactive_batches'];

// Initialize age category counters
$chicks_1_7_days = 0;
$chicks_8_14_days = 0;
$chicks_15_21_days = 0;
$chicks_22_28_days = 0;
$chicks_29_days_and_above = 0;
$totalChicks = 0;

// Current date
$currentDate = date('Y-m-d');

// SQL to get BATCHICKS, total mortality for each batch, and batch start date
$query = "
    SELECT 
        b.BATSNO,
        b.BATDDT,
        b.BATCHICKS,
        IFNULL(SUM(v.VISMORTALITY), 0) AS total_mortality
    FROM batmast b
    LEFT JOIN visitmast v ON v.VITBATSNO = b.BATSNO AND v.VISDDT <= '$currentDate'
    WHERE b.BATACTFLG = 1
    GROUP BY b.BATSNO
";

$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $batchChicks = $row['BATCHICKS'];
        $mortality = $row['total_mortality'];
        $batchDate = $row['BATDDT'];

        // Calculate remaining birds
        $balanceBirds = $batchChicks - $mortality;
        if ($balanceBirds < 0) $balanceBirds = 0; // Just in case

        // Calculate age
        $ageInDays = (strtotime($currentDate) - strtotime($batchDate)) / (60 * 60 * 24);

        // Categorize by age
        if ($ageInDays >= 1 && $ageInDays <= 7) {
            $chicks_1_7_days += $balanceBirds;
        } elseif ($ageInDays >= 8 && $ageInDays <= 14) {
            $chicks_8_14_days += $balanceBirds;
        } elseif ($ageInDays >= 15 && $ageInDays <= 21) {
            $chicks_15_21_days += $balanceBirds;
        } elseif ($ageInDays >= 22 && $ageInDays <= 28) {
            $chicks_22_28_days += $balanceBirds;
        } elseif ($ageInDays >= 29) {
            $chicks_29_days_and_above += $balanceBirds;
        }

        $totalChicks += $balanceBirds;
    }

} else {
    echo "Error: " . mysqli_error($conn);
}

// Close database connection
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Visit | Farm Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/home1.css">
    <link rel="stylesheet" href="./css/table.css">

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
                <a href="./view/area.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-map-marked-alt"></i></div>
                    <div class="nav-label">Areas</div>
                    <!-- Image Suggestion: Map with farm locations -->
                </a>
                <a href="./view/breeds.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-dna"></i></div>
                    <div class="nav-label">Breeds</div>
                    <!-- Image Suggestion: Different chicken breed comparison -->
                </a>
                <a href="./view/fieldoff.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-user-shield"></i></div>
                    <div class="nav-label">Field Officers</div>
                    <!-- Image Suggestion: Agriculture officer inspecting chickens -->
                </a>
                <a href="./view/farms.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-tractor"></i></div>
                    <div class="nav-label">Farmers</div>
                    <!-- Image Suggestion: Aerial view of poultry farm -->
                </a>
                
                <a href="./view/batches.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-boxes"></i></div>
                    <div class="nav-label">Batches</div>
                    <!-- Image Suggestion: Poultry chicks in a brooder -->
                </a>
                
                
                

                <!-- New Additional Options -->
                <a href="./view/visit.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-utensils"></i></div>
                    <div class="nav-label">Visit Management</div>
                    <!-- Image Suggestion: Poultry feed in bags or feeding system -->
                </a>
                <a href="./view/gin.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-clipboard-list"></i></div>
                    <div class="nav-label">Good Issue Note (GIN)</div>
                    <!-- Image Suggestion: Veterinarian checking chickens -->
                </a>
                <a href="./view/production.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-chart-pie"></i></div>
                    <div class="nav-label">Production</div>
                    <!-- Image Suggestion: Eggs collection or growth chart -->
                </a>
                <a href="./view/inventory.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-clipboard-list"></i></div>
                    <div class="nav-label">Inventory</div>
                    <!-- Image Suggestion: Farm equipment and supplies -->
                </a>
                <a href="./view/reports.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-file-alt"></i></div>
                    <div class="nav-label">Reports</div>
                    <!-- Image Suggestion: Analytics dashboard or report document -->
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
                    <h1 class="dashboard-title">Farm Dashboard</h1>
                    <div class="dashboard-actions">
                        <button class="btn btn-secondary">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <button class="btn btn-primary" onclick="window.location.href='./add/visitadd.php'">
                            <i class="fas fa-plus"></i> Add visit
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Total Farmers</div>
                            <div class="stat-icon" style="background-color: rgba(79, 70, 229, 0.1); color: var(--primary);">
                                <i class="fas fa-warehouse"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo $totalFarmers; ?></div>
                        <div class="stat-change positive">
                            <table class="chick-stats-table">
                                <tr>
                                    <td>Active Farmers</td>
                                    <td> <?php echo $activeFarmers; ?></td>
                                </tr>


                        </div>

                        <div class="stat-change negative">

                            <tr>
                                <td style="color:#ef4444;">Inactive Farmers</td>
                                <td style="color:#ef4444;"> <?php echo $inactiveFarmers; ?></td>
                            </tr>

                            </table>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Total Batches</div>
                            <div class="stat-icon" style="background-color: rgba(16, 185, 129, 0.1); color: var(--success);">
                                <i class="fas fa-egg"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo $totalBatches; ?></div>
                        <div class="stat-change positive">
                            <table class="chick-stats-table">
                                <tr>
                                    <td>Active Batches</td>
                                    <td> <?php echo $activeBatches; ?></td>
                                </tr>


                        </div>

                        <div class="stat-change negative">

                            <tr>
                                <td style="color:#ef4444;">Inactive Batches</td>
                                <td style="color:#ef4444;"> <?php echo $inactiveBatches; ?></td>
                            </tr>

                            </table>
                        </div>

                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Avg. Yield Rate</div>
                            <div class="stat-icon" style="background-color: rgba(245, 158, 11, 0.1); color: var(--accent);">
                                <i class="fas fa-percentage"></i>
                            </div>
                        </div>
                        <div class="stat-value">92.5%</div>
                        <div class="stat-change negative">
                            <i class="fas fa-arrow-down"></i> 1.2% from last month
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Workers Active</div>
                            <div class="stat-icon" style="background-color: rgba(239, 68, 68, 0.1); color: var(--danger);">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="stat-value">48</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i> 3 new today
                        </div>
                    </div>
                </div>
                <div class="stats-grid">
                    <div class="stat-card" style="margin-bottom: 50px;">
                        <div class="stat-header">
                            <div class="stat-title">Total Chicks</div>
                            <div class="stat-icon" style="background-color: rgba(16, 185, 129, 0.1); color: var(--success);">
                                <i class="fas fa-kiwi-bird"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo $totalChicks; ?></div>
                        <div class="stat-change positive">
                            <table class="chick-stats-table">
                                <tr>
                                    <td>1 to 7 Days:</td>
                                    <td><?php echo $chicks_1_7_days; ?></td>
                                </tr>
                                <tr>
                                    <td>8 to 14 Days:</td>
                                    <td><?php echo $chicks_8_14_days; ?></td>
                                </tr>
                                <tr>
                                    <td>15 to 21 Days:</td>
                                    <td><?php echo $chicks_15_21_days; ?></td>
                                </tr>
                                <tr>
                                    <td>22 to 28 Days:</td>
                                    <td><?php echo $chicks_22_28_days; ?></td>
                                </tr>
                                <tr>
                                    <td>29 days and more:</td>
                                    <td><?php echo $chicks_29_days_and_above; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>






                <!-- Charts Section -->
                <div class="charts-section">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Production Trends</h3>
                            <div class="chart-actions">
                                <button class="btn btn-secondary" style="padding: 0.5rem;">Weekly</button>
                                <button class="btn btn-secondary" style="padding: 0.5rem;">Monthly</button>
                                <button class="btn btn-secondary" style="padding: 0.5rem;">Yearly</button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <div class="chart-placeholder">Production Chart (Would be replaced with Chart.js or similar)</div>
                        </div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Farm Distribution</h3>
                            <div class="chart-actions">
                                <button class="btn btn-secondary" style="padding: 0.5rem;">View All</button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <div class="chart-placeholder">Pie Chart (Would be replaced with Chart.js or similar)</div>
                        </div>
                    </div>
                </div>



                <!-- Recent Activity -->
                <div class="activity-card">
                    <div class="activity-header">
                        <h3 class="activity-title">Recent Activity</h3>
                        <button class="btn btn-secondary" style="padding: 0.5rem 1rem;">View All</button>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-egg"></i>
                            </div>
                            <div class="activity-details">
                                <div class="activity-description">New batch created at Farm #12</div>
                                <div class="activity-time">10 minutes ago</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="activity-details">
                                <div class="activity-description">Feed delivery completed for Farm #8</div>
                                <div class="activity-time">1 hour ago</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-details">
                                <div class="activity-description">New worker added to the system</div>
                                <div class="activity-time">3 hours ago</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="activity-details">
                                <div class="activity-description">Monthly report generated</div>
                                <div class="activity-time">Yesterday</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="action-title">Add New Farm</div>
                    </div>
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-egg"></i>
                        </div>
                        <div class="action-title">Create Batch</div>
                    </div>
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="action-title">Generate Report</div>
                    </div>
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="action-title">Set Reminder</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- In a real implementation, you would include charting libraries like Chart.js -->
    <script>
        // This would be replaced with actual chart initialization
        console.log("Charts would be initialized here with Chart.js or similar library");

        // Sample animation for demonstration
        document.querySelectorAll('.stat-card').forEach((card, index) => {
            card.style.animationDelay = `${0.1 * index}s`;
        });
        document.addEventListener('DOMContentLoaded', function() {
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const sidebar = document.querySelector('.sidebar');
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  
  // Toggle sidebar
  mobileMenuBtn.addEventListener('click', function() {
    sidebar.classList.toggle('active');
    sidebarOverlay.classList.toggle('active');
  });
  
  // Close sidebar when clicking overlay
  sidebarOverlay.addEventListener('click', function() {
    sidebar.classList.remove('active');
    sidebarOverlay.classList.remove('active');
  });
  
  // Close sidebar when clicking a nav item (optional)
  const navItems = document.querySelectorAll('.nav-item');
  navItems.forEach(item => {
    item.addEventListener('click', function() {
      if (window.innerWidth <= 768) {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
      }
    });
  });
});
    </script>
</body>

</html>