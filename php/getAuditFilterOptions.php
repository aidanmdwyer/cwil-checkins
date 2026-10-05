<?php
session_start();
include 'requireKey.php';
isKeyValid();

require_once 'db.php';

header('Content-Type: application/json');

$buildingNameResults = $conn->query(
    "SELECT DISTINCT buildingName FROM closet_audits"
);

$auditorResults = $conn->query(
    "SELECT DISTINCT auditor FROM closet_audits"
);

$icResults = $conn->query(
    "SELECT DISTINCT ic FROM closet_audits"
);

if (!$buildingNameResults || !$auditorResults || !$icResults) {
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

while ($row = $icResults->fetch_assoc()) {
    $icRows[] = $row['ic'];
}

$conn->close();

echo json_encode([
    'buildingNames' => $buildingNameRows,
    'auditors' => $auditorRows,
    'ics' => $icRows
]);