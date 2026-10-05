<?php
session_start();

include 'requireKey.php';
isKeyValid();

$auditId = $_GET['auditId'] ?? null;

if (!$auditId && $auditId !== 0) {
    http_response_code(404);
    die('Page load failed.');
}

require_once 'db.php';

$selectAuditItemsStmt = $conn->prepare(
    "SELECT a.auditId, a.itemId, a.itemDescription, a.itemValue, b.comment
        FROM closet_audit_items a
        LEFT JOIN closet_audit_comments b
            ON a.auditId = b.auditId 
        AND a.itemId = b.itemId
        AND a.auditId = ?
        WHERE a.auditId = ?;"
);

$auditId = (int)$auditId;

$selectAuditItemsStmt->bind_param("ii", $auditId, $auditId);
$selectAuditItemsStmt->execute();

$selectAuditItemsResult = $selectAuditItemsStmt->get_result();

$rows = [];

while ($row = $selectAuditItemsResult->fetch_assoc()) {
    $rows[] = $row;
}

$selectAuditItemsStmt->close();
$conn->close();

echo json_encode($rows);