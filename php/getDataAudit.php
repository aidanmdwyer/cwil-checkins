<?php
session_start();
include 'requireKey.php';
isKeyValid();

require_once 'db.php';

$filterFromDate = $_GET['filterFromDate'];
$filterToDate = $_GET['filterToDate'];
$filterBuildingName = $_GET['filterBuildingName'];
$filterAuditor = $_GET['filterAuditor'];
$filterIC = $_GET['filterIC'];

$selectAuditsSQL = "SELECT * FROM closet_audits";

$selectAuditsFilters = [];
$selectAuditsTypeString = "";
$selectAuditsValues = [];
if($filterFromDate && $filterToDate) {
    $selectAuditsFilters[] = "date > ?";
    $selectAuditsTypeString .= "s";
    $selectAuditsValues[] = $filterFromDate;
    $selectAuditsFilters[] = "date < ?";
    $selectAuditsTypeString .= "s";
    $selectAuditsValues[] = $filterToDate;
}
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
    $selectAuditsSQL .= " WHERE " . implode(" AND ", $selectAuditsFilters);
}

$selectAuditsSQL .= " ORDER BY date DESC;";

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