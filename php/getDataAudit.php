<?php
session_start();
include 'requireKey.php';
isKeyValid();

require_once 'db.php';

$selectAuditsSQL = "SELECT * FROM closet_audits";
$filterBuildingName = $_GET['filterBuildingName'];
$filterAuditor = $_GET['filterAuditor'];
$filterIC = $_GET['filterIC'];

if($filterBuildingName || $filterAuditor || $filterIC) {
    $selectAuditsSQL .= " WHERE";
    if($filterBuildingName) {
        $selectAuditsSQL .= " buildingName = ?";
    }
    if($filterAuditor) {
        $selectAuditsSQL .= " auditor = ?";
    }
    if($filterIC) {
        $selectAuditsSQL .= " ic = ?";
    }
}

$conn->close();

?>