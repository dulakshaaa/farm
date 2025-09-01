<?php
include '../includes/connect.php'; // / adjust to your DB connection file

header('Content-Type: application/json');

// Collect POST data safely
$locid      = isset($_POST['locid']) ? intval($_POST['locid']) : 0;
$grnno      = $_POST['grnno'] ?? '';
$grnddt     = $_POST['grnddt'] ?? date("Y-m-d");
$grntime    = $_POST['grntime'] ?? date("H:i:s");
$supid      = isset($_POST['supid']) ? intval($_POST['supid']) : 0;
$invoiceno  = $_POST['invoiceno'] ?? null;
$inhremarks = $_POST['inhremarks'] ?? null;
$inhtotal = $_POST['grandTotal'] ?? null;

$sql = "INSERT INTO inhtran (inhdno, age) VALUES ('$name', $age)";

