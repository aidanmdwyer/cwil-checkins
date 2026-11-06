<?php
session_start();
include 'requireKey.php';
isKeyValid();

require_once 'db.php';

header('Content-Type: application/json');

$isContractor = $_SESSION['accountType'] === "contractor";

$buildingNameSQL = "SELECT DISTINCT buildingName FROM closet_audits";
if($isContractor) {
    $buildingNameSQL .= " WHERE ic = ?";
    $buildingNameStmt = $conn->prepare($buildingNameSQL);
    $buildingNameStmt->bind_param("s", $_SESSION['username']);
    $buildingNameStmt->execute();
    $buildingNameResults = $buildingNameStmt->get_result();
} else {
    $buildingNameResults = $conn->query($buildingNameSQL);
}

$auditorSQL = "SELECT DISTINCT auditor FROM closet_audits";
if($isContractor) {
    $auditorSQL .= " WHERE ic = ?";
    $auditorStmt = $conn->prepare($auditorSQL);
    $auditorStmt->bind_param("s", $_SESSION['username']);
    $auditorStmt->execute();
    $auditorResults = $auditorStmt->get_result();
} else {
    $auditorResults = $conn->query($auditorSQL);
}

if(!$isContractor) {
    $icResults = $conn->query(
        "SELECT DISTINCT ic FROM closet_audits"
    );
}

if (!$buildingNameResults || !$auditorResults || (!$icResults && !$isContractor)) {
    http_response_code(500);

    echo json_encode([
        'error' => $conn->error
    ]);

    exit;
}

$buildingNameRows = [];
$auditorRows = [];
$icRows = [];

while ($row = $buildingNameResults->fetch_assoc()) {
    $buildingNameRows[] = $row['buildingName'];
}

while ($row = $auditorResults->fetch_assoc()) {
    $auditorRows[] = $row['auditor'];
}

if(!$isContractor) {
    while ($row = $icResults->fetch_assoc()) {
        $icRows[] = $row['ic'];
    }
}

$conn->close();

if($isContractor) {
    echo json_encode([
        'buildingNames' => $buildingNameRows,
        'auditors' => $auditorRows
    ]);
} else {
    echo json_encode([
        'buildingNames' => $buildingNameRows,
        'auditors' => $auditorRows,
        'ics' => $icRows
    ]);
}
