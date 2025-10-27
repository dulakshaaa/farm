<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'connect2.php'; // DB connection

error_log("fill.php: Request received with POST data: " . json_encode($_POST)); // Debug log

if (!isset($_POST['id'])) {
    error_log("fill.php: No GRN ID provided");
    echo json_encode(["status" => "error", "message" => "No GRN ID provided"]);
    exit;
}

$id = intval($_POST['id']);
error_log("fill.php: Processing GRN ID: $id"); // Debug log

try {
    $sql = "SELECT h.INHSNO, h.INHDNO, h.INHINVNO, h.INHTYPSNO, h.INHLOCSNO, 
                   h.INHDDT, h.INHTOT, h.INHREM, h.INHSUPSNO,
                   l.INLLNO, l.INLSTKSNO, l.INLQTY, l.INLUNTSNO, l.INLCOST, l.INLVAT, l.INLDIS, l.INLTOTAL,
                   i.stkdesc AS ItemName, u.sysdes1 AS UnitName, s.supnam AS SupplierName, loc.locnam AS LocationName
            FROM inhtran h
            LEFT JOIN inltran l ON h.INHSNO = l.INLINHSNO
            LEFT JOIN stkmast i ON l.INLSTKSNO = i.stksno
            LEFT JOIN sysmast u ON l.INLUNTSNO = u.syssno
            LEFT JOIN supmast s ON h.INHSUPSNO = s.supsno
            LEFT JOIN locmast loc ON h.INHLOCSNO = loc.locsno
            WHERE h.INHSNO = ?";

    error_log("fill.php: Executing query: $sql with INHSNO=$id"); // Debug log

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $data = ['details' => []];
        while ($row = $result->fetch_assoc()) {
            if (!isset($data['INHSNO'])) {
                $data['INHSNO'] = $row['INHSNO'];
                $data['INHDNO'] = $row['INHDNO'];
                $data['INHINVNO'] = $row['INHINVNO'];
                $data['INHTYPSNO'] = $row['INHTYPSNO'];
                $data['INHLOCSNO'] = $row['INHLOCSNO'];
                $data['INHDDT'] = $row['INHDDT'];
                $data['INHTOT'] = $row['INHTOT'];
                $data['INHREM'] = $row['INHREM'] ?? '';
                $data['INHSUPSNO'] = $row['INHSUPSNO'] ?? '';
                $data['SupplierName'] = $row['SupplierName'] ?? 'N/A';
                $data['LocationName'] = $row['LocationName'] ?? 'N/A';
                $data['INHTIME'] = $row['INHDDT'] && $row['INHDDT'] !== '0000-00-00' ? date('H:i', strtotime($row['INHDDT'])) : '00:00';
            }
            if ($row['INLLNO']) {
                $data['details'][] = [
                    'INLLNO' => $row['INLLNO'],
                    'INLSTKSNO' => $row['INLSTKSNO'],
                    'INLQTY' => $row['INLQTY'],
                    'INLUNTSNO' => $row['INLUNTSNO'],
                    'INLCOST' => $row['INLCOST'],
                    'INLVAT' => $row['INLVAT'],
                    'INLDIS' => $row['INLDIS'],
                    'INLTOTAL' => $row['INLTOTAL'],
                    'ItemName' => $row['ItemName'] ?? 'N/A',
                    'UnitName' => $row['UnitName'] ?? 'N/A'
                ];
            }
        }
        error_log("fill.php: Data retrieved: " . json_encode($data)); // Debug log
        echo json_encode(["status" => "success", "data" => $data]);
    } else {
        error_log("fill.php: No GRN found for ID: $id");
        echo json_encode(["status" => "error", "message" => "No GRN found for ID: $id"]);
    }

    $stmt->close();
} catch (Exception $e) {
    error_log("fill.php: Error: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}

$conn->close();
?>