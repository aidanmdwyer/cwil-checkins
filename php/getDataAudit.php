<?php
session_start();
include 'requireKey.php';
isKeyValid();

require_once 'db.php';

$selectAuditsSQL = "SELECT * FROM closet_audits";
$filterBuildingName = $_GET['filterBuildingName'];
$filterAuditor = $_GET['filterAuditor'];
$filterIC = $_GET['filterIC'];

$selectAuditsFilters = [];
$selectAuditsTypeString = "";
$selectAuditsValues = [];
if($filterBuildingName && $filterBuildingName !== "---") {
    $selectAuditsFilters[] = "buildingName = ?";
    $selectAuditsTypeString .= "s";
    $selectAuditsValues[] = $filterBuildingName;
}
if($filterAuditor && $filterAuditor !== "---") {
    $selectAuditsFilters[] = "auditor = ?";
    $selectAuditsTypeString .= "s";
    $selectAuditsValues[] = $filterAuditor;
}
if($filterIC && $filterIC !== "---") {
    $selectAuditsFilters[] = "ic = ?";
    $selectAuditsTypeString .= "s";
    $selectAuditsValues[] = $filterIC;
}

if(count($selectAuditsFilters) > 0) {
    $selectAuditsSQL .= " WHERE " . implode(" AND ", $selectAuditsFilters) . " ORDER BY date ASC;";
}

$selectAuditsStmt = $conn->prepare($selectAuditsSQL);
if(count($selectAuditsFilters) > 0) {
    $selectAuditsStmt->bind_param($selectAuditsTypeString, ...$selectAuditsValues);
}
$selectAuditsStmt->execute();

$selectAuditResult = $selectAuditsStmt->get_result();

$rows = [];
if($selectAuditResult) {
    while ($row = $selectAuditResult->fetch_assoc()) {
        $rows[] = $row;
    }
}

$selectAuditsStmt->close();
$conn->close();

echo json_encode($rows);
?>