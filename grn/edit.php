<?php
header('Content-Type: application/json');

require_once 'connect2.php';

function sendResponse($status, $message, $data = null) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['header']) || !isset($input['lines'])) {
        error_log("edit.php: Invalid JSON input or missing header/lines at " . date('Y-m-d H:i:s'));
        sendResponse('error', 'Invalid input data');
    }

    $header = $input['header'];
    $lines = $input['lines'];

    // Validate header
    if (!isset($header['id']) || !isset($header['locid']) || !isset($header['invoiceno']) || 
        !isset($header['grnddt']) || !isset($header['grntime']) || !isset($header['grandTotal'])) {
        error_log("edit.php: Missing required header fields: " . json_encode($header));
        sendResponse('error', 'Missing required header fields');
    }

    $grnId = $header['id'];
    $locid = $header['locid'];
    $invoiceno = $header['invoiceno'];
    $grnddt = $header['grnddt'];
    $grntime = $header['grntime'];
    $inhremarks = isset($header['inhremarks']) ? $header['inhremarks'] : null;
    $supid = isset($header['supid']) && $header['supid'] !== '' ? $header['supid'] : null;
    $grandTotal = $header['grandTotal'];

    // Validate numeric fields
    if (!is_numeric($grnId) || !is_numeric($locid) || !is_numeric($grandTotal)) {
        error_log("edit.php: Invalid numeric fields in header: id=$grnId, locid=$locid, grandTotal=$grandTotal");
        sendResponse('error', 'Invalid numeric fields in header');
    }

    // Validate date and time
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $grnddt)) {
        error_log("edit.php: Invalid date format: $grnddt");
        sendResponse('error', 'Invalid date format');
    }
    if (!preg_match('/^\d{2}:\d{2}$/', $grntime)) {
        error_log("edit.php: Invalid time format: $grntime");
        sendResponse('error', 'Invalid time format');
    }

    // Combine date and time for INHDDT
    $inhddt = "$grnddt $grntime:00";

    // Validate location exists
    $stmt = $conn->prepare("SELECT locsno FROM locmast WHERE locsno = ?");
    $stmt->bind_param("i", $locid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        error_log("edit.php: Invalid location ID: $locid");
        sendResponse('error', 'Invalid location ID');
    }
    $stmt->close();

    // Validate supplier if provided
    if ($supid !== null) {
        $stmt = $conn->prepare("SELECT supsno FROM supmast WHERE supsno = ?");
        $stmt->bind_param("i", $supid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            error_log("edit.php: Invalid supplier ID: $supid");
            sendResponse('error', 'Invalid supplier ID');
        }
        $stmt->close();
    }

    // Validate GRN exists
    $stmt = $conn->prepare("SELECT INHSNO FROM inhtran WHERE INHSNO = ?");
    $stmt->bind_param("i", $grnId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        error_log("edit.php: GRN not found: $grnId");
        sendResponse('error', 'GRN not found');
    }
    $stmt->close();

    // Validate line items
    if (empty($lines)) {
        error_log("edit.php: No line items provided");
        sendResponse('error', 'At least one line item is required');
    }

    foreach ($lines as $index => $line) {
        if (!isset($line['itemid']) || !isset($line['unit']) || !isset($line['quantity']) || 
            !isset($line['cost']) || !isset($line['vat']) || !isset($line['dis']) || !isset($line['total'])) {
            error_log("edit.php: Missing required fields for line $index: " . json_encode($line));
            sendResponse('error', "Missing required fields for line $index");
        }

        $itemid = $line['itemid'];
        $unit = $line['unit'];
        $quantity = $line['quantity'];
        $cost = $line['cost'];
        $vat = $line['vat'];
        $dis = $line['dis'];
        $total = $line['total'];

        // Validate numeric fields
        if (!is_numeric($itemid) || !is_numeric($unit) || !is_numeric($quantity) || 
            !is_numeric($cost) || !is_numeric($vat) || !is_numeric($dis) || !is_numeric($total)) {
            error_log("edit.php: Invalid numeric fields for line $index: " . json_encode($line));
            sendResponse('error', "Invalid numeric fields for line $index");
        }

        // Validate item exists
        $checkStmt = $conn->prepare("SELECT stksno FROM stkmast WHERE stksno = ?");
        $checkStmt->bind_param("i", $itemid);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        if ($result->num_rows === 0) {
            error_log("edit.php: Invalid item ID for line $index: $itemid");
            sendResponse('error', "Invalid item ID for line $index");
        }
        $checkStmt->close();

        // Validate unit exists
        $checkStmt = $conn->prepare("SELECT syssno FROM sysmast WHERE syssno = ?");
        $checkStmt->bind_param("i", $unit);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        if ($result->num_rows === 0) {
            error_log("edit.php: Invalid unit ID for line $index: $unit");
            sendResponse('error', "Invalid unit ID for line $index");
        }
        $checkStmt->close();

        // Verify total calculation
        $calculatedTotal = $quantity * $cost * (1 + $vat / 100) - $dis;
        if (abs($calculatedTotal - $total) > 0.01) {
            error_log("edit.php: Total mismatch for line $index. Expected: $calculatedTotal, Received: $total");
            sendResponse('error', "Total mismatch for line $index");
        }
    }

    // Start transaction
    $conn->begin_transaction();

    // Update inhtran
    $stmt = $conn->prepare(
        "UPDATE inhtran SET INHLOCSNO = ?, INHINVNO = ?, INHDDT = ?, INHTOT = ?, INHREM = ?, INHSUPSNO = ? WHERE INHSNO = ?"
    );
    $stmt->bind_param(
        "issdsii",
        $locid,
        $invoiceno,
        $inhddt,
        $grandTotal,
        $inhremarks,
        $supid,
        $grnId
    );
    if (!$stmt->execute()) {
        error_log("edit.php: Failed to update inhtran for INHSNO=$grnId: " . $stmt->error);
        $conn->rollback();
        sendResponse('error', 'Failed to update header');
    }
    $stmt->close();

    // Fetch existing inltran rows ordered by INLLNO
    $stmt = $conn->prepare("SELECT INLLNO FROM inltran WHERE INLINHSNO = ? ORDER BY INLLNO");
    $stmt->bind_param("i", $grnId);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingLines = [];
    while ($row = $result->fetch_assoc()) {
        $existingLines[] = $row['INLLNO'];
    }
    $stmt->close();

    // Update or insert line items
    $updateStmt = $conn->prepare(
        "UPDATE inltran SET INLSTKSNO = ?, INLQTY = ?, INLUNTSNO = ?, INLCOST = ?, INLVAT = ?, INLDIS = ?, INLTOTAL = ? 
         WHERE INLINHSNO = ? AND INLLNO = ?"
    );
    $updateStmt->bind_param(
        "idddddddi",
        $itemid,
        $quantity,
        $unit,
        $cost,
        $vat,
        $dis,
        $total,
        $grnId,
        $inllno
    );

    $insertStmt = $conn->prepare(
        "INSERT INTO inltran (INLINHSNO, INLSTKSNO, INLQTY, INLUNTSNO, INLCOST, INLVAT, INLDIS, INLTOTAL) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $insertStmt->bind_param(
        "iiddsddd",
        $grnId,
        $itemid,
        $quantity,
        $unit,
        $cost,
        $vat,
        $dis,
        $total
    );

    foreach ($lines as $index => $line) {
        $itemid = $line['itemid'];
        $unit = $line['unit'];
        $quantity = $line['quantity'];
        $cost = $line['cost'];
        $vat = $line['vat'];
        $dis = $line['dis'];
        $total = $line['total'];

        error_log("edit.php: Processing line $index - itemid=$itemid, unit=$unit, quantity=$quantity, cost=$cost, vat=$vat, dis=$dis, total=$total");

        if (isset($existingLines[$index])) {
            // Update existing line
            $inllno = $existingLines[$index];
            $updateStmt->bind_param(
                "idddddddi",
                $itemid,
                $quantity,
                $unit,
                $cost,
                $vat,
                $dis,
                $total,
                $grnId,
                $inllno
            );
            if (!$updateStmt->execute()) {
                error_log("edit.php: Failed to update line $index (INLLNO=$inllno) for INLINHSNO=$grnId: " . $updateStmt->error);
                $conn->rollback();
                sendResponse('error', "Failed to update line $index");
            }
        } else {
            // Insert new line
            $insertStmt->bind_param(
                "iiddsddd",
                $grnId,
                $itemid,
                $quantity,
                $unit,
                $cost,
                $vat,
                $dis,
                $total
            );
            if (!$insertStmt->execute()) {
                error_log("edit.php: Failed to insert line $index for INLINHSNO=$grnId: " . $insertStmt->error);
                $conn->rollback();
                sendResponse('error', "Failed to insert line $index");
            }
        }
    }

    $updateStmt->close();
    $insertStmt->close();

    // Commit transaction
    $conn->commit();
    error_log("edit.php: Successfully updated GRN INHSNO=$grnId with " . count($lines) . " line items");
    sendResponse('success', 'GRN updated successfully');

} catch (Exception $e) {
    error_log("edit.php: Exception at " . date('Y-m-d H:i:s') . ": " . $e->getMessage());
    $conn->rollback();
    sendResponse('error', 'Server error: ' . $e->getMessage());
}

$conn->close();
?>