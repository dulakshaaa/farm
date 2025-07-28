<?php
require_once '../includes/connect.php';
require_login();  // This will redirect to login if not authenticated

require_once '../includes/geolocation_access.php';

// Get current user data
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT * FROM usemast WHERE USRSNO = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$current_user = $user_query->get_result()->fetch_assoc();
include '../includes/c-visitnavbar.php';

// Fetch farmers for dropdown
$farmers = [];
$farmerQuery = "SELECT FARSNO, FARNAME FROM farma WHERE FARACTFLG = 1 ORDER BY FARNAME";
$farmerResult = $conn->query($farmerQuery);
if ($farmerResult) {
    while ($row = $farmerResult->fetch_assoc()) {
        $farmers[$row['FARSNO']] = $row['FARNAME'];
    }
}

// Fetch batches for dropdown
$batches = [];
$batchQuery = "SELECT b.BATCODE, b.BATSNO, b.BATDDT, b.BATCHICKS, b.batblnbrd, b.BATFARSNO, f.FARFLOSNO 
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
            'chicks' => $row['batblnbrd'],
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
    // Validate and sanitize input
    $visfarsno = filter_input(INPUT_POST, 'FARMER_ID', FILTER_VALIDATE_INT);
    $visbatcode = filter_input(INPUT_POST, 'VISBATCODE', FILTER_SANITIZE_STRING);
    $visfieldoff = filter_input(INPUT_POST, 'VISFIELDOFF', FILTER_VALIDATE_INT);
    $visddt = filter_input(INPUT_POST, 'VISDDT', FILTER_SANITIZE_STRING);
    $vismortality = filter_input(INPUT_POST, 'VISMORTALITY', FILTER_VALIDATE_FLOAT);
    $visavgwgt = filter_input(INPUT_POST, 'VISAVGWGT', FILTER_VALIDATE_FLOAT);
    $visinpfeedbag = filter_input(INPUT_POST, 'VISINPFEEDBAG', FILTER_VALIDATE_FLOAT);
    $visfeedbal = filter_input(INPUT_POST, 'VISFEEDBAL', FILTER_VALIDATE_FLOAT);
    $viscgcon = filter_input(INPUT_POST, 'VISCGCON', FILTER_SANITIZE_STRING);
    $visgenremarks = filter_input(INPUT_POST, 'VISGENREMARKS', FILTER_SANITIZE_STRING);
    $visremarks = filter_input(INPUT_POST, 'VISREMARKS', FILTER_SANITIZE_STRING);



    // Validate required fields
    if (
        !$visfarsno || !$visbatcode || !$visfieldoff || !$visddt ||
        $vismortality === false || $visavgwgt === false ||
        $visinpfeedbag === false || $visfeedbal === false
    ) {
        echo "<script>
                alert('Please fill all required fields with valid data');
                window.history.back();
              </script>";
        exit;
    }

    // Get batch details
    $batchQuery = "SELECT BATSNO, BATDDT, BATCHICKS, batblnbrd FROM batmast WHERE BATCODE = ?";
    $stmt = $conn->prepare($batchQuery);
    $stmt->bind_param("s", $visbatcode);
    $stmt->execute();
    $batchResult = $stmt->get_result();
    $batchDetails = $batchResult->fetch_assoc();
    $stmt->close();

    if (!$batchDetails) {
        echo "<script>
                alert('Error: The provided batch code does not exist.');
                window.history.back();
              </script>";
        exit;
    }

    $visbatsno = $batchDetails['BATSNO'];

    // Calculate age in days
    try {
        $batchDate = new DateTime($batchDetails['BATDDT']);
        $visitDate = new DateTime($visddt);

        // Ensure visit date is not before batch date
        if ($visitDate < $batchDate) {
            echo "<script>
                    alert('Error: Visit date cannot be before batch date.');
                    window.history.back();
                  </script>";
            exit;
        }

        $interval = $batchDate->diff($visitDate);
        $visage = $interval->days;
    } catch (Exception $e) {
        echo "<script>
                alert('Error: Invalid date format.');
                window.history.back();
              </script>";
        exit;
    }

    // Calculate derived fields
    $vismotpcn = ($vismortality / $batchDetails['batblnbrd']) * 100;
    $visblnbird = $batchDetails['batblnbrd'] - $vismortality;


    // Calculate feed balance
    $visfeedconsumed = $visinpfeedbag - $visfeedbal;

    // Calculate FCR (Feed Conversion Ratio)
    $total_weight = $visavgwgt * $visblnbird; // Total weight in kg
    $total_feed_consumed_kg = $visfeedconsumed * 50; // Convert bags to kg (assuming 1 bag = 50kg)
    $visfcr = ($total_weight > 0) ? ($total_feed_consumed_kg / $total_weight) : 0;
    $visavgfeed = $total_feed_consumed_kg / $visblnbird; // Average feed per bird

    // Set user and IP information
    $visadduser = $current_user['USRNAME'];
    $visaddip = $_SERVER['REMOTE_ADDR'];
    $visusrsno = $user_id;

    // Insert data using prepared statement
    $sql = "INSERT INTO visitmast (
                VISFARSNO, VITBATSNO, VISFIELDOFF, VISDDT, VISAGE, VISMORTALITY, VISMOTPCN, 
                VISBLNBIRD, VISAVGWGT, VISAVGFEED, VISFCR,
                VISINPFEEDBAG, VISFEEDCONSUMED, VISFEEDBAL, VISADDUSER, VISADDIP, VISUSRSNO,VISCGCON, VISREMARKS, VISGENREMARKS
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "<script>
                alert('Error preparing statement: " . addslashes($conn->error) . "');
                window.history.back();
              </script>";
        exit;
    }

    $stmt->bind_param(
        "iiisididdddddsssisss",  // now 17 characters
        $visfarsno,
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
        $visinpfeedbag,
        $visfeedconsumed,
        $visfeedbal,
        $visadduser,
        $visaddip,
        $visusrsno,
        $viscgcon,          // new
        $visgenremarks,     // new
        $visremarks,        // new
    );


    if ($stmt->execute()) {
        // ✅ UPDATE batmast.batblnbird after successful insert
        $update_sql = "UPDATE batmast SET batblnbrd = ? WHERE batsno = ?";
        $update_stmt = $conn->prepare($update_sql);

        if ($update_stmt) {
            $update_stmt->bind_param("ii", $visblnbird, $visbatsno);
            if ($update_stmt->execute()) {
                echo "<script>
                alert('Visit added and batch bird count updated.');
                window.location.href = 'c-visitadd.php'; // or your desired page
            </script>";
            } else {
                echo "<script>
                alert('Visit added, but failed to update batch bird count.');
                window.history.back();
            </script>";
            }
            $update_stmt->close();
        } else {
            echo "<script>
            alert('Visit added, but failed to prepare batch update: " . addslashes($conn->error) . "');
            window.history.back();
        </script>";
        }
    } else {
        echo "<script>
        alert('Error inserting visit: " . addslashes($stmt->error) . "');
        window.history.back();
    </script>";
    }

    $stmt->close();
}
// Fetch farmers for dropdown with area information
$farmers = [];
$farmerQuery = "SELECT f.FARSNO, f.FARNAME, f.FARAREASNO, a.AREANAME 
                FROM farma f 
                LEFT JOIN areamast a ON f.FARAREASNO = a.AREASNO 
                WHERE f.FARACTFLG = 1 
                ORDER BY f.FARNAME";
$farmerResult = $conn->query($farmerQuery);
if ($farmerResult) {
    while ($row = $farmerResult->fetch_assoc()) {
        $farmers[$row['FARSNO']] = [
            'name' => $row['FARNAME'],
            'area_sno' => $row['FARAREASNO'],
            'area_name' => $row['AREANAME'] ?? 'No Area Assigned'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Visit Record</title>
    <link rel="stylesheet" href="../css/visadd.css">
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
            margin-bottom: 10px;
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
        $(document).ready(function() {
            // Initialize data from PHP
            const farmers = <?php echo json_encode($farmers); ?>;
            const batchesByFarmer = <?php echo json_encode($batches); ?>;
            const allFieldOfficers = <?php echo json_encode($fieldOfficers); ?>;

            // Function to populate batches dropdown based on selected farmer
            function updateBatchesDropdown() {
                const farmerId = $('#FARMER_ID').val();
                $('#VISBATCODE').empty().append('<option value="">-- Select Batch --</option>');

                if (farmerId && batchesByFarmer[farmerId]) {
                    batchesByFarmer[farmerId].forEach(function(batch) {
                        $('#VISBATCODE').append(
                            `<option value="${batch.code}" 
                         data-flosno="${batch.flosno}"
                         data-chicks="${batch.chicks}"
                         data-date="${batch.date}">
                         ${batch.code} (${batch.date})
                         </option>`
                        );
                    });
                }
                $('#VISBATCODE').prop('disabled', !farmerId);
            }

            // Function to set field officer based on selected batch
            function setFieldOfficer() {
                const selectedBatch = $('#VISBATCODE option:selected');
                const flosno = selectedBatch.data('flosno');
                if (flosno && allFieldOfficers[flosno]) {
                    $('#VISFIELDOFF').val(flosno).trigger('change');
                }
            }

            // Function to update area field based on selected farmer
            function updateAreaField() {
                const farmerId = $('#FARMER_ID').val();
                if (farmerId && farmers[farmerId]) {
                    $('#VISAREA').val(farmers[farmerId].area_name);
                } else {
                    $('#VISAREA').val('No Area Assigned');
                }
            }

            function calculateFields() {
                const farmerId = $('#FARMER_ID').val();
                const batchCode = $('#VISBATCODE').val();
                const visitDate = $('#VISDDT').val();
                const mortality = parseFloat($('#VISMORTALITY').val()) || 0;
                const avgWgt = parseFloat($('#VISAVGWGT').val()) || 0;
                const inpFeedBag = parseFloat($('#VISINPFEEDBAG').val()) || 0;
                const feedBal = parseFloat($('#VISFEEDBAL').val()) || 0;
                const birdbal = parseFloat($('#VISBLNBIRD').val()) || 0;

                const feedConsumed = inpFeedBag - feedBal;
                $('#VISFEEDCONSUMED').val(feedConsumed.toFixed(2));

                if (batchCode && visitDate) {
                    const selectedBatch = $('#VISBATCODE option:selected');
                    const batchDateStr = selectedBatch.data('date');
                    const totalChicks = parseFloat(selectedBatch.data('chicks')) || 0;

                    try {
                        const batchDate = new Date(batchDateStr);
                        const visitDateObj = new Date(visitDate);

                        if (visitDateObj < batchDate) {
                            alert('Visit date cannot be before batch date');
                            $('#VISDDT').val('');
                            return;
                        }

                        const diffTime = visitDateObj - batchDate;
                        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                        $('#VISAGE').val(diffDays);

                        if (totalChicks > 0) {
                            const motPcn = (mortality / totalChicks) * 100;
                            $('#VISMOTPCN').val(motPcn.toFixed(2));
                        }

                        const balanceBirds = totalChicks - mortality;
                        $('#VISBLNBIRD').val(balanceBirds);
                    } catch (e) {
                        console.error('Date calculation error:', e);
                    }
                }

                const balanceBirds = parseFloat($('#VISBLNBIRD').val()) || 0;
                const totalFeedKg = feedConsumed * 50;
                const totalWeight = avgWgt * balanceBirds;
                const avgFeed = totalFeedKg / balanceBirds;
                $('#VISAVGFEED').val(avgFeed.toFixed(2));

                if (totalWeight > 0) {
                    const fcr = (totalFeedKg / totalWeight);
                    $('#VISFCR').val(fcr.toFixed(2));
                } else {
                    $('#VISFCR').val('0.00');
                }


            }

            // Attach event listeners
            $('#FARMER_ID').on('change', function() {
                updateBatchesDropdown();
                updateAreaField(); // Update area field when farmer changes
                calculateFields();
            });

            $('#VISBATCODE').on('change', function() {
                setFieldOfficer();
                calculateFields();
            });

            $('#VISDDT, #VISMORTALITY, #VISAVGWGT, #VISINPFEEDBAG, #VISFEEDBAL').on('change keyup', calculateFields);

            $('#VISFIELDOFF').empty().append('<option value="">-- Select Field Officer --</option>');
            $.each(allFieldOfficers, function(id, name) {
                $('#VISFIELDOFF').append(`<option value="${id}">${name}</option>`);
            });

            // Initial setup
            updateBatchesDropdown();
            updateAreaField(); // Set initial area
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
                            <label for="FARMER_ID">Farmer Name *</label>
                            <select id="FARMER_ID" name="FARMER_ID" required>
                                <option value="">-- Select Farmer --</option>
                                <?php foreach ($farmers as $id => $farmer): ?>
                                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($farmer['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="VISAREA">Farmer Area</label>
                            <input type="text" id="VISAREA" name="VISAREA" readonly class="calculated-field">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="VISBATCODE">Batch Code *</label>
                            <select id="VISBATCODE" name="VISBATCODE" required disabled>
                                <option value="">-- Select Batch --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="VISFIELDOFF">Field Officer *</label>
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
                            <label for="VISDDT">Visit Date *</label>
                            <input type="date" id="VISDDT" name="VISDDT" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="VISAGE">Age (days)</label>
                            <input type="number" id="VISAGE" name="VISAGE" readonly class="calculated-field">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="VISMORTALITY">Mortality Count *</label>
                            <input type="number" id="VISMORTALITY" name="VISMORTALITY" min="0" step="1" required>
                        </div>
                        <div class="form-group">
                            <label for="VISMOTPCN">Mortality %</label>
                            <input type="number" id="VISMOTPCN" name="VISMOTPCN" step="0.01" readonly class="calculated-field">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="VISBLNBIRD">Balance Birds</label>
                            <input type="number" id="VISBLNBIRD" name="VISBLNBIRD" min="0" readonly class="calculated-field">
                        </div>
                        <div class="form-group">
                            <label for="VISAVGWGT">Avg Weight (kg) *</label>
                            <input type="number" id="VISAVGWGT" name="VISAVGWGT" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="form-column">




                    <div class="form-row">
                        <div class="form-group">
                            <label for="VISINPFEEDBAG">Input Feed (bags) *</label>
                            <input type="number" id="VISINPFEEDBAG" name="VISINPFEEDBAG" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="VISFEEDBAL">Feed Balance (bags) *</label>
                            <input type="number" id="VISFEEDBAL" name="VISFEEDBAL" step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="VISFEEDCONSUMED">Feed Consumed (bags)</label>
                            <input type="number" id="VISFEEDCONSUMED" name="VISFEEDCONSUMED" step="0.01" readonly class="calculated-field">
                        </div>
                        <div class="form-group">
                            <label for="VISAVGFEED">Average Feed</label>
                            <input type="number" id="VISAVGFEED" name="VISAVGFEED" step="0.01" readonly class="calculated-field">
                        </div>


                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="VISFCR">Feed Conversion Ratio (FCR)</label>
                            <input type="number" id="VISFCR" name="VISFCR" step="0.01" readonly class="calculated-field">
                        </div>
                        <div class="form-group">
                            <label for="VISCGCON">Cage Condition</label>
                            <select name="VISCGCON" id="VISCGCON" required>
                                <option value="">.....</option>
                                <option value="op1">op1</option>
                                <option value="op2">op2</option>
                                <option value="op3">op3</option>
                                <option value="op4">op4</option>
                            </select>

                        </div>


                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="VISREMARKS">Remarks</label>
                            <textarea id="VISREMARKS" name="VISREMARKS" placeholder="Enter any remarks or observations..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="VISGENREMARKS"> General Remarks</label>
                            <textarea id="VISGENREMARKS" name="VISGENREMARKS" placeholder="Enter any remarks or observations..."></textarea>
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