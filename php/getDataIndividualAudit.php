<?php
session_start();

include 'requireKey.php';
isKeyValid();

$auditId = $_GET['auditId'] ?? null;

header('Content-Type: text/plain');

var_dump($_GET);
var_dump($auditId);

require_once 'db.php';

$stmt = $conn->prepare(
    "SELECT * FROM closet_audit_items WHERE auditId = ?"
);

var_dump($stmt);

$stmt->bind_param("i", (int)$auditId);

var_dump($stmt->execute());

$result = $stmt->get_result();

var_dump($result);

while ($row = $result->fetch_assoc()) {
    var_dump($row);
}