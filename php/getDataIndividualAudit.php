<?php
session_start();

include 'requireKey.php';
isKeyValid();

$auditId = $_GET['auditId'] ?? null;

require_once 'db.php';

$stmt = $conn->prepare(
    "SELECT * FROM closet_audit_items WHERE auditId = ?"
);

var_dump("prepare", $stmt);

$auditId = (int)$auditId;

var_dump("auditId", $auditId);

$bind = $stmt->bind_param("i", $auditId);

var_dump("bind", $bind);

$execute = $stmt->execute();

var_dump("execute", $execute);
var_dump("stmt error", $stmt->error);

$result = $stmt->get_result();

var_dump("result", $result);
var_dump("conn error", $conn->error);

$rows = [];

while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

var_dump("rows", $rows);

$stmt->close();
$conn->close();