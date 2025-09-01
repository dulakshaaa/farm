<?php
include '../includes/connect.php'; // DB connection

header('Content-Type: application/json');

if (!isset($_POST['header_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing header ID']);
    exit;
}

$header_id = intval($_POST['header_id']);
$item_ids  = $_POST['item_id'] ?? [];
$units     = $_POST['item_unit'] ?? [];
$quantities= $_POST['quantity'] ?? [];
$costs     = $_POST['Cost'] ?? [];
$vats      = $_POST['Vat'] ?? [];
$discounts = $_POST['Dis'] ?? [];
$totals    = $_POST['total'] ?? [];

if (empty($item_ids)) {
    echo json_encode(['status' => 'error', 'message' => 'No detail lines']);
    exit;
}

$conn->begin_transaction();
try {
    $lineNo = 1;
    $grandTotal = 0;

    $stmt = $conn->prepare("
        INSERT INTO inltran
        (INLINHSNO, INLLNO, INLSTKSNO, INLQTY, INLUNTSNO, INLDDT, INLADDUSR, INLADDDT, INLADDTIME, INLADDIP) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $today = date("Y-m-d");
    $now   = date("H:i:s");
    $ip    = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $user  = 'system'; // replace with logged-in user if available

    foreach ($item_ids as $i => $item_id) {
        $qty   = floatval($quantities[$i] ?? 0);
        $unit  = intval($units[$i] ?? 0);   // must match sysmast.syssno
        $total = floatval($totals[$i] ?? 0);

        $stmt->bind_param(
            "iiidisssss",
            $header_id,
            $lineNo,
            $item_id,
            $qty,
            $unit,
            $today,
            $user,
            $today,
            $now,
            $ip
        );
        if (!$stmt->execute()) {
            throw new Exception("Insert failed: " . $stmt->error);
        }

        $grandTotal += $total;
        $lineNo++;
    }

    // update header with grand total
    $stmt2 = $conn->prepare("UPDATE inhtran SET INHTOT = ? WHERE INHSNO = ?");
    $stmt2->bind_param("di", $grandTotal, $header_id);
    if (!$stmt2->execute()) {
        throw new Exception("Failed to update header total: " . $stmt2->error);
    }

    $conn->commit();
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
