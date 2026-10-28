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
$page = $_GET['page'] >= 1 ? $_GET['page'] : 1;

$selectAuditsSQL = "SELECT * FROM closet_audits";

$selectAuditsFilters = [];
$selectAuditsTypeString = "";
$selectAuditsValues = [];
if($filterFromDate && $filterToDate) {
    $selectAuditsFilters[] = "date >= ?";
    $selectAuditsTypeString .= "s";
    $selectAuditsValues[] = $filterFromDate;

    $modFilterToDate = new DateTime($filterToDate);
    $modFilterToDate->modify('+1 day');

    $selectAuditsFilters[] = "date < ?";
    $selectAuditsTypeString .= "s";
    $selectAuditsValues[] = $modFilterToDate->format('Y-m-d');
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

//separate query for counting total rows for filter, done before pagination parameter
$countSQL = "SELECT COUNT(*) AS totalRows FROM closet_audits";

if (count($selectAuditsFilters) > 0) {
    $countSQL .= " WHERE " . implode(" AND ", $selectAuditsFilters);
}

$countStmt = $conn->prepare($countSQL);
if (count($selectAuditsFilters) > 0) {
    $countStmt->bind_param($selectAuditsTypeString, ...$selectAuditsValues);
}
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['totalRows'];
$numPages = ceil($totalRows / 10);

$countStmt->close();

//back to select query
if(count($selectAuditsFilters) > 0) {
    $selectAuditsSQL .= " WHERE " . implode(" AND ", $selectAuditsFilters);
}

$selectAuditsSQL .= " ORDER BY date DESC LIMIT ?, 10";
$selectAuditsTypeString .= "i";
$selectAuditsValues[] = ($page-1) * 10;

$selectAuditsStmt = $conn->prepare($selectAuditsSQL);
$selectAuditsStmt->bind_param($selectAuditsTypeString, ...$selectAuditsValues);
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

echo json_encode([
    'page' => $page,
    'numPages' => $numPages,
    'rows' => $rows
    ]);
?>