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

$selectAuditImageStmt = $conn->prepare("SELECT TO_BASE64(closetImage) AS closetImage FROM closet_audits WHERE auditId = ?;");
$selectAuditImageStmt->bind_param("i", $auditId);
$selectAuditImageStmt->execute();
$selectAuditImageResult = $selectAuditImageStmt->get_result();
$imageRow = $selectAuditImageResult->fetch_assoc();
$closetImage = $imageRow['closetImage'] ?? null;
$selectAuditImageStmt->close();

$selectAuditItemsStmt = $conn->prepare(
    "SELECT a.auditId, a.itemId, a.itemDescription, a.itemValue, b.comment, TO_BASE64(c.image) AS image
        FROM closet_audit_items a
        LEFT JOIN closet_audit_comments b
            ON a.auditId = b.auditId 
        	AND a.itemId = b.itemId
        LEFT JOIN closet_audit_images c
            ON a.auditId = c.auditId 
        	AND a.itemId = c.itemId
        WHERE a.auditId = ?;"
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

echo json_encode([
    'closetImage' => $closetImage,
    'items' => $rows
    ]);