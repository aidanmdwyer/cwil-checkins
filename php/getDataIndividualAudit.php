<?php
session_start();

include 'requireKey.php';
isKeyValid();

$auditId = $_GET['auditId'] ?? null;

if (!$auditId) {
    http_response_code(404);
    die('Page load failed.');
}

require_once 'db.php';

$selectAuditItemsStmt = $conn->prepare(
    "SELECT * FROM closet_audits_items WHERE auditId = ?"
);

if (!$selectAuditItemsStmt) {
    http_response_code(500);
    die('Prepare failed: ' . $conn->error);
}

$selectAuditItemsStmt->bind_param("i", (int)$auditId);

if (!$selectAuditItemsStmt->execute()) {
    http_response_code(500);
    die('Execute failed: ' . $selectAuditItemsStmt->error);
}

$selectAuditItemsResult = $selectAuditItemsStmt->get_result();

if (!$selectAuditItemsResult) {
    http_response_code(500);
    die('Get result failed: ' . $selectAuditItemsStmt->error);
}

$rows = [];

while ($row = $selectAuditItemsResult->fetch_assoc()) {
    $rows[] = $row;
}

$selectAuditItemsStmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($rows);
?>