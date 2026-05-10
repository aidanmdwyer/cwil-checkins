<?php
session_start();
include 'requireKey.php';
isKeyValid();

require_once 'db.php';

$archiveDate = $_GET['archiveDate'] ?? '';

//Build SQL query


if($_SESSION['accountType'] === 'contractor') {
    $filterIc = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT * FROM archive WHERE archiveDate = ? AND ic = ? ORDER BY name");
    $stmt->bind_param("ss", $archiveDate, $filterIc);
} else {
    $stmt = $conn->prepare("SELECT * FROM archive WHERE archiveDate = ? ORDER BY name");
    $stmt->bind_param("s", $archiveDate);
}
$stmt->execute();
$result = $stmt->get_result();

//Fetch and return rows
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);

$stmt->close();
$conn->close();
?>