<?php
include '../includes/connect.php'; // adjust path
header('Content-Type: application/json');

$locid      = isset($_POST['locid']) ? intval($_POST['locid']) : 0;
$typeid     = 101; // document type
$supid      = isset($_POST['supid']) ? intval($_POST['supid']) : 0;
$invoiceno  = $_POST['invoiceno'] ?? null;
$inhremarks = $_POST['inhremarks'] ?? null;
$inhtotal   = floatval($_POST['grandTotal'] ?? 0.00);
$grnddt     = $_POST['grnddt'] ?? date("Y-m-d");

// Fixed year/month (adjust as needed)
$year = date("Y");
$month = date("n");

try {
    $conn->begin_transaction();

    // Generate GRN number
    $stmt = $conn->prepare("SELECT * FROM ndnmast 
                            WHERE ndnlocsno = ? AND ndnyear = ? AND ndnmon = ? AND ndntypsno = ? 
                            FOR UPDATE");
    $stmt->bind_param("iiii", $locid, $year, $month, $typeid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nextNo = $row['ndnnxtdocno'];
        $grnNo = sprintf("GRN/%s/%05d", $locid, $nextNo);

        // Update next number
        $newNextNo = $nextNo + 1;
        $updateStmt = $conn->prepare("UPDATE ndnmast SET ndnnxtdocno = ? WHERE ndnsno = ?");
        $updateStmt->bind_param("ii", $newNextNo, $row['ndnsno']);
        $updateStmt->execute();
    } else {
        $nextNo = 1;
        $grnNo = sprintf("GRN/%s/%05d", $locid, $nextNo);

        $ndnlocsno = $locid;
        $ndnyear = $year;
        $ndnmon = $month;
        $ndnnxtdocno = $nextNo + 1;
        $ndntypsno = $typeid;
        $ndnaddusr = 'current_user'; // replace with session username
        $ndnadddt = date('Y-m-d');

        $insertStmt = $conn->prepare("INSERT INTO ndnmast 
            (ndnlocsno, ndnyear, ndnmon, ndnnxtdocno, ndntypsno, ndnaddusr, ndnadddt) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertStmt->bind_param("iiiiiss", $ndnlocsno, $ndnyear, $ndnmon, $ndnnxtdocno, $ndntypsno, $ndnaddusr, $ndnadddt);
        $insertStmt->execute();
    }

    // Insert header
    $insertInh = $conn->prepare("INSERT INTO inhtran 
        (INHDNO, INHINVNO, INHTYPSNO, INHLOCSNO, INHDDT, INHTOT, INHSUPSNO, INHREM) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insertInh->bind_param("ssiiddss", $grnNo, $invoiceno, $typeid, $locid, $grnddt, $inhtotal, $supid, $inhremarks);
    $insertInh->execute();

    $headerId = $conn->insert_id; // This is the FK for GRN lines

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "id" => $headerId,
        "grnno" => $grnNo
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
