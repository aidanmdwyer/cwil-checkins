<?php
session_start();
include 'requireKey.php';
isKeyValid();

require_once 'db.php';

$auditId = $_GET['auditId'];

$selectAuditStmt = $conn->prepare("SELECT * FROM closet_audits WHERE auditId = ?");
$selectAuditStmt->bind_param("i", $auditId);
$selectAuditStmt->execute();

$selectAuditResult = $selectAuditStmt->get_result();

$row = $selectAuditResult->fetch_assoc();

$selectAuditsStmt->close();
$conn->close();

echo json_encode($row);
?>