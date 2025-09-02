<?php
include '../includes/connect.php'; // adjust path
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'No data saved'];

if (isset($_POST['header_id'])) {
    $headerId = intval($_POST['header_id']);

    if ($headerId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid header ID']);
        exit;
    }

    $itemIds    = isset($_POST['itemid']) && is_array($_POST['itemid']) ? $_POST['itemid'] : [];
    $units      = isset($_POST['unit']) && is_array($_POST['unit']) ? $_POST['unit'] : [];
    $quantities = isset($_POST['quantity']) && is_array($_POST['quantity']) ? $_POST['quantity'] : [];
    $costs      = isset($_POST['Cost']) && is_array($_POST['Cost']) ? $_POST['Cost'] : [];
    $vats       = isset($_POST['Vat']) && is_array($_POST['Vat']) ? $_POST['Vat'] : [];
    $discounts  = isset($_POST['Dis']) && is_array($_POST['Dis']) ? $_POST['Dis'] : [];
    $totals     = isset($_POST['total']) && is_array($_POST['total']) ? $_POST['total'] : [];

    $success = true;

    // Start transaction
    $conn->begin_transaction();

    try {
        for ($i = 0; $i < count($itemIds); $i++) {
            $itemId   = intval($itemIds[$i]);
            $unitId   = intval($units[$i]);
            $qty      = floatval($quantities[$i]);
            $cost     = floatval($costs[$i]);
            $vat      = floatval($vats[$i]);
            $dis      = floatval($discounts[$i]);
            $total    = floatval($totals[$i]);
            $lineDate = date('Y-m-d');
            $lineNo   = $i + 1;

            // Skip empty rows
            if ($itemId > 0 && $qty > 0 && $unitId > 0) {
                $sql = "INSERT INTO inltran 
                        (INLINHSNO, INLLNO, INLSTKSNO, INLUNTSNO, INLQTY, INLDDT, INLCOST, INLVAT, INLDIS, INLTOTAL)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param(
                    "iiiidddddd",
                    $headerId, $lineNo, $itemId, $unitId, $qty, $lineDate, $cost, $vat, $dis, $total
                );

                if (!$stmt->execute()) {
                    $success = false;
                    throw new Exception("Failed line $lineNo: " . $stmt->error);
                }
            }
        }

        $conn->commit();

        $response = ['status' => 'success', 'message' => 'GRN lines saved successfully!'];

    } catch (Exception $e) {
        $conn->rollback();
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }
}

echo json_encode($response);
?>
