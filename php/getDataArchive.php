<?php
session_start();
include 'requireKey.php';
isKeyValid();

require_once 'db.php';

$archiveFromDate = $_GET['archiveFromDate'] ?? '';
$archiveToDate = $_GET['archiveToDate'] ?? '';

//Build SQL query

if($archiveToDate) { //range
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
} else { //single day
    if($_SESSION['accountType'] === 'contractor') {
        $filterIc = $_SESSION['username'];
        $stmt = $conn->prepare("SELECT * FROM archive WHERE archiveDate = ? AND ic = ? ORDER BY name");
        $stmt->bind_param("ss", $archiveFromDate, $filterIc);
    } else {
        $stmt = $conn->prepare("SELECT * FROM archive WHERE archiveDate = ? ORDER BY name");
        $stmt->bind_param("s", $archiveFromDate);
    }
    $stmt->execute();
    $result = $stmt->get_result();
}

//Fetch and return rows
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);

$stmt->close();
$conn->close();
?>