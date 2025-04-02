<?php
require_once '../includes/connect.php';
require_login();  // This will redirect to login if not authenticated

// 2. Get current user data if needed
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM usemast WHERE USRSNO = $user_id");
$current_user = $user_query->fetch_assoc();
include '../includes/fieldoffnavbar.php';

// Fetch farmers for dropdown
$farmers = [];
$farmerQuery = "SELECT FARSNO, FARNAME FROM farma WHERE FARACTFLG = 1 ORDER BY FARNAME";
$farmerResult = $conn->query($farmerQuery);
if ($farmerResult) {
    while ($row = $farmerResult->fetch_assoc()) {
        $farmers[$row['FARSNO']] = $row['FARNAME'];
    }
}

// Fetch batches for dropdown (will be filtered by farmer)
$batches = [];
$batchQuery = "SELECT b.BATCODE, b.BATSNO, b.BATDDT, b.BATCHICKS, b.BATFARSNO, f.FARFLOSNO 
               FROM batmast b 
               JOIN farma f ON b.BATFARSNO = f.FARSNO
               WHERE b.BATACTFLG = 1
               ORDER BY b.BATCODE";
$batchResult = $conn->query($batchQuery);
if ($batchResult) {
    while ($row = $batchResult->fetch_assoc()) {
        $batches[$row['BATFARSNO']][] = [
            'code' => $row['BATCODE'],
            'sno' => $row['BATSNO'],
            'date' => $row['BATDDT'],
            'chicks' => $row['BATCHICKS'],
            'flosno' => $row['FARFLOSNO'] 
        ];
    }
}

// Fetch all field officers for dropdown
$fieldOfficers = [];
$officerQuery = "SELECT FLOSNO, FLONAME FROM flomast WHERE FLOACTFLG = 1 ORDER BY FLONAME";
$officerResult = $conn->query($officerQuery);
if ($officerResult) {
    while ($row = $officerResult->fetch_assoc()) {
        $fieldOfficers[$row['FLOSNO']] = $row['FLONAME'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $visbatcode = $_POST['VISBATCODE'];
    $visfieldoff = $_POST['VISFIELDOFF'];
    $visddt = $_POST['VISDDT'];
    $vismortality = $_POST['VISMORTALITY'];
    $visblnbird = $_POST['VISBLNBIRD'];
    $visavgwgt = $_POST['VISAVGWGT'];
    $visavgfeed = $_POST['VISAVGFEED'];
    $viscgcon = $_POST['VISCGCON'];
    $visinpfeedbag = $_POST['VISINPFEEDBAG'];
    $visfeedconsumed = $_POST['VISFEEDCONSUMED'];

    // Get BATSNO from BATCODE
    $batchQuery = "SELECT BATSNO, BATDDT, BATCHICKS FROM batmast WHERE BATCODE = ?";
    $stmt = $conn->prepare($batchQuery);
    $stmt->bind_param("s", $visbatcode);
    $stmt->execute();
    $batchResult = $stmt->get_result();
    $batchDetails = $batchResult->fetch_assoc();
    $stmt->close();

    if (!$batchDetails) {
        echo "<script>
                alert('Error: The provided batch code does not exist.');
                window.location.href = 'your_previous_page.php';
              </script>";
        exit;
    }

    $visbatsno = $batchDetails['BATSNO'];
    $batchDate = new DateTime($batchDetails['BATDDT']);
    $visitDate = new DateTime($visddt);
    $interval = $batchDate->diff($visitDate);
    $visage = $interval->days;

    // Calculate derived fields
    $vismotpcn = ($vismortality / $batchDetails['BATCHICKS']) * 100;
    $visfcr = $visavgfeed / $visavgwgt;
    $visfeedbal = $visinpfeedbag - $visfeedconsumed;

    // Set user and IP information
    $visadduser = 'Admin'; // Replace with actual user from session
    $visaddip = $_SERVER['REMOTE_ADDR'];

    // Insert data using prepared statement
    $sql = "INSERT INTO visitmast (
                VITBATSNO, VISFIELDOFF, VISDDT, VISAGE, VISMORTALITY, VISMOTPCN, 
                VISBLNBIRD, VISAVGWGT, VISAVGFEED, VISFCR, VISCGCON, 
                VISINPFEEDBAG, VISFEEDCONSUMED, VISFEEDBAL, VISADDUSER, VISADDIP
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "iisididddddsidds",
        $visbatsno,
        $visfieldoff,
        $visddt,
        $visage,
        $vismortality,
        $vismotpcn,
        $visblnbird,
        $visavgwgt,
        $visavgfeed,
        $visfcr,
        $viscgcon,
        $visinpfeedbag,
        $visfeedconsumed,
        $visfeedbal,
        $visadduser,
        $visaddip
    );

    if ($stmt->execute()) {
        echo "<script>
                alert('Visit record added successfully');
                window.location.href = '../view/visitview.php';
              </script>";
    } else {
        echo "<script>
                alert('Error: " . addslashes($conn->error) . "');
              </script>";
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Visit Record</title>
    <link rel="stylesheet" href="../css/visitadd.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .calculated-field {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            margin-top: 5px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .form-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }

        .form-actions button[type="reset"] {
            background-color: #e0e0e0;
        }

        .form-actions button[type="submit"] {
            background-color: #4CAF50;
            color: white;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    // Set today's date as default for visit date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('VISDDT').value = today;
});
        $(document).ready(function() {
            // Initialize data from PHP
            const farmers = <?php echo json_encode($farmers); ?>;
            const batchesByFarmer = <?php echo json_encode($batches); ?>;
            const allFieldOfficers = <?php echo json_encode($fieldOfficers); ?>;

            // Function to populate batches dropdown based on selected farmer
            function updateBatchesDropdown() {
                const farmerId = $('#FARMER_ID').val();
                
                // Update batches dropdown
                $('#VISBATCODE').empty().append('<option value="">-- Select Batch --</option>');
                if (farmerId && batchesByFarmer[farmerId]) {
                    batchesByFarmer[farmerId].forEach(function(batch) {
                        $('#VISBATCODE').append(`<option value="${batch.code}" data-flosno="${batch.flosno}">${batch.code} (${batch.date})</option>`);
                    });
                }
                
                // Enable/disable batch selection based on farmer selection
                $('#VISBATCODE').prop('disabled', !farmerId);
            }

            // Function to set field officer based on selected batch
            function setFieldOfficer() {
                const selectedBatch = $('#VISBATCODE option:selected');
                const flosno = selectedBatch.data('flosno');
                
                if (flosno && allFieldOfficers[flosno]) {
                    $('#VISFIELDOFF').val(flosno);
                } else {
                    $('#VISFIELDOFF').val('');
                }
            }

            function calculateFields() {
                const farmerId = $('#FARMER_ID').val();
                const batchCode = $('#VISBATCODE').val();
                const visitDate = $('#VISDDT').val();
                const mortality = parseFloat($('#VISMORTALITY').val()) || 0;
                const avgWgt = parseFloat($('#VISAVGWGT').val()) || 0;
                const avgFeed = parseFloat($('#VISAVGFEED').val()) || 0;
                const inpFeedBag = parseFloat($('#VISINPFEEDBAG').val()) || 0;
                const feedConsumed = parseFloat($('#VISFEEDCONSUMED').val()) || 0;

                // Calculate age if we have batch and visit date
                if (batchCode && visitDate && farmerId && batchesByFarmer[farmerId]) {
                    // Find the selected batch
                    const selectedBatch = batchesByFarmer[farmerId].find(b => b.code == batchCode);
                    
                    if (selectedBatch) {
                        // Calculate age
                        const batchDate = new Date(selectedBatch.date);
                        const visitDateObj = new Date(visitDate);
                        const diffTime = visitDateObj - batchDate;
                        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                        $('#VISAGE').val(diffDays);

                        // Calculate mortality percentage
                        if (selectedBatch.chicks > 0) {
                            const motPcn = (mortality / selectedBatch.chicks) * 100;
                            $('#VISMOTPCN').val(motPcn.toFixed(2));
                        }

                        // Calculate Balance Birds
                        const balanceBirds = selectedBatch.chicks - mortality;
                        $('#VISBLNBIRD').val(balanceBirds);
                    }
                }

                // Calculate FCR
                if (avgWgt > 0) {
                    const fcr = avgFeed / avgWgt;
                    $('#VISFCR').val(fcr.toFixed(2));
                }

                // Calculate feed balance
                const feedBal = inpFeedBag - feedConsumed;
                $('#VISFEEDBAL').val(feedBal.toFixed(2));
            }

            // Attach event listeners
            $('#FARMER_ID').on('change', function() {
                updateBatchesDropdown();
                calculateFields();
            });

            $('#VISBATCODE').on('change', function() {
                setFieldOfficer();
                calculateFields();
            });

            $('#VISDDT, #VISMORTALITY, #VISAVGWGT, #VISAVGFEED, #VISINPFEEDBAG, #VISFEEDCONSUMED').on('change keyup', calculateFields);

            // Initialize field officers dropdown
            $('#VISFIELDOFF').empty().append('<option value="">-- Select Field Officer --</option>');
            $.each(allFieldOfficers, function(id, name) {
                $('#VISFIELDOFF').append(`<option value="${id}">${name}</option>`);
            });

            // Initial setup
            updateBatchesDropdown();
        });
    </script>
</head>

<body>
    <div class="form-container">
        <form method="POST" action="">
            <h1>Add Visit Record</h1>
            <div class="form-columns">
                <!-- Left Column -->
                <div class="form-column">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="FARMER_ID">Farmer Name</label>
                            <select id="FARMER_ID" name="FARMER_ID" required>
                                <option value="">-- Select Farmer --</option>
                                <?php foreach ($farmers as $id => $name): ?>
                                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="VISBATCODE">Batch Code</label>
                            <select id="VISBATCODE" name="VISBATCODE" required disabled>
                                <option value="">-- Select Batch --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="VISFIELDOFF">Field Officer</label>
                            <select id="VISFIELDOFF" name="VISFIELDOFF" required>
                                <option value="">-- Select Field Officer --</option>
                                <?php foreach ($fieldOfficers as $id => $name): ?>
                                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                    <div class="form-group">
    <label for="VISDDT">Visit Date</label>
    <input type="date" id="VISDDT" name="VISDDT" readonly 
           value="<?php echo date('Y-m-d'); ?>">
</div>
                        <div class="form-group">
                            <label for="VISAGE">Age (days)</label>
                            <input type="number" id="VISAGE" name="VISAGE" readonly class="calculated-field">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="VISMORTALITY">Mortality Count</label>
                            <input type="number" id="VISMORTALITY" name="VISMORTALITY" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="VISMOTPCN">Mortality %</label>
                            <input type="number" id="VISMOTPCN" name="VISMOTPCN" step="0.01" readonly class="calculated-field">
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="form-column">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="VISBLNBIRD">Balance Birds</label>
                            <input type="number" id="VISBLNBIRD" name="VISBLNBIRD" min="0" readonly class="calculated-field">
                        </div>
                        <div class="form-group">
                            <label for="VISAVGWGT">Avg Weight (kg)</label>
                            <input type="number" id="VISAVGWGT" name="VISAVGWGT" step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="VISAVGFEED">Avg Feed (kg)</label>
                            <input type="number" id="VISAVGFEED" name="VISAVGFEED" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="VISFCR">Feed Conversion Ratio (FCR)</label>
                            <input type="number" id="VISFCR" name="VISFCR" step="0.01" readonly class="calculated-field">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="VISCGCON">Feed Consumed (kg)</label>
                            <input type="number" id="VISCGCON" name="VISCGCON" step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="VISINPFEEDBAG">Input Feed (bags)</label>
                            <input type="number" id="VISINPFEEDBAG" name="VISINPFEEDBAG" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="VISFEEDCONSUMED">Feed Consumed (bags)</label>
                            <input type="number" id="VISFEEDCONSUMED" name="VISFEEDCONSUMED" step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="VISFEEDBAL">Feed Balance (kg)</label>
                            <input type="number" id="VISFEEDBAL" name="VISFEEDBAL" step="0.01" readonly class="calculated-field">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="reset">Reset</button>
                <button type="submit">Save</button>
            </div>
        </form>
    </div>
</body>

</html>