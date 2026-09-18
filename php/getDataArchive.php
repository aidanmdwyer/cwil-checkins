<?php
session_start();
include 'requireKey.php';
isKeyValid();

require_once 'db.php';

$archiveSingleDate = $_GET['archiveSingleDate'] ?? '';
$archiveFromDate = $_GET['archiveFromDate'] ?? '';
$archiveToDate = $_GET['archiveToDate'] ?? '';

if($archiveSingleDate) { //single day
    if($_SESSION['accountType'] === 'contractor') {
        $filterIc = $_SESSION['username'];
        $stmt = $conn->prepare("SELECT * FROM archive WHERE archiveDate = ? AND ic = ? ORDER BY name");
        $stmt->bind_param("ss", $archiveSingleDate, $filterIc);
    } else {
        $stmt = $conn->prepare("SELECT * FROM archive WHERE archiveDate = ? ORDER BY name");
        $stmt->bind_param("s", $archiveSingleDate);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else if($archiveToDate > $archiveFromDate && ($archiveToDate - $archiveFromDate) <= 31) { //range
    if($_SESSION['accountType'] === 'contractor') {
        $filterIc = $_SESSION['username'];
        $stmt = $conn->prepare("SELECT * FROM archive WHERE archiveDate >= ? AND archiveDate <= ? ORDER BY archiveDate, name");
        $stmt->bind_param("sss", $archiveFromDate, $archiveToDate, $filterIc);
    } else {
        $stmt = $conn->prepare("SELECT * FROM archive WHERE archiveDate >= ? AND archiveDate <= ? ORDER BY archiveDate, name");
        $stmt->bind_param("ss", $archiveFromDate, $archiveToDate);
    }
    $stmt->execute();
    $result = $stmt->get_result();
}

//Fetch and return rows
$rows = [];
if($result) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

echo json_encode($rows);

$stmt->close();
$conn->close();
?>