<?php
session_start();
include 'requireKey.php';
isKeyValid();

require_once 'db.php';

$buildingNameResults = $conn->query("SELECT DISTINCT buildingName FROM closet_audits;");
$auditorResults = $conn->query("SELECT DISTINCT auditor FROM closet_audits;");
$icResults = $conn->query("SELECT DISTINCT ic FROM closet_audits;");

$buildingNameRows = [];
$auditorRows = [];
$icRows = [];
if($buildingNameResults) {
    while ($row = $buildingNameResults->fetch_assoc()) {
        $buildingNameRows[] = $row;
    }
}
if($auditorResults) {
    while ($row = $auditorResults->fetch_assoc()) {
        $auditorRows[] = $row;
    }
}
if($icResults) {
    while ($row = $icResults->fetch_assoc()) {
        $icRows[] = $row;
    }
}

$conn->close();

echo json_encode([
    'buildingNames' => $buildingNameRows,
    'auditors' => $auditorRows,
    'ics' => $icRows
]);
?>