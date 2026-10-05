<?php
session_start();
include 'requireKey.php';
isKeyValid();

$auditId = $_GET['auditId'];
if(!$auditId) {
    http_response_code(404);
    die('Page load failed.');
}

require_once 'db.php';

$selectAuditItemsStmt = $conn->prepare("SELECT * FROM closet_audits_items WHERE auditId = ?");
$selectAuditItemsStmt->bind_param("i", $auditId);
$selectAuditItemsStmt->execute();

$selectAuditItemsResult = $selectAuditItemsStmt->get_result();

$rows = [];
if($selectAuditItemsResult) {
    while ($row = $selectAuditItemsResult->fetch_assoc()) {
        $rows[] = $row;
    }
}

$selectAuditItemsStmt->close();
$conn->close();

echo json_encode($rows);
?>