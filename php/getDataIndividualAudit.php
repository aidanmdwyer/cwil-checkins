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
    "SELECT * FROM closet_audit_items WHERE auditId = ?"
);

$auditId = (int)$auditId;

$selectAuditItemsStmt->bind_param("i", $auditId);
$selectAuditItemsStmt->execute();

$selectAuditItemsResult = $selectAuditItemsStmt->get_result();

$rows = [];

while ($row = $selectAuditItemsResult->fetch_assoc()) {
    $rows[] = $row;
}

$selectAuditItemsStmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($rows);