<?php
include 'login.php';
include 'accountProperties.php';

if (!accountProperties('Accounts Page')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to access this page.');
}

// Connect to database
require_once 'db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['properties']) && isset($_POST['accountName']) && isset($_POST['accountType'])) {
    $accountName = rawurldecode($_POST['accountName']);
    $accountType = rawurldecode($_POST['accountType']);

    if($_POST['accountName'] === 'default') {
        $insName = rawurldecode($_POST['accountType']);
    } else {
        $insName = rawurldecode($_POST['accountName']);
    }

    $params = [];

    $sql = "INSERT INTO accountProperties (accountName, property, permission) VALUES ";

    $propertyCount = 0;
    foreach($_POST['properties'] as $property => $permission) {
        $propertyCount++;
        $params[] = $insName;
        $params[] = rawurldecode($property);
        $params[] = (int) $permission;
    }
    $sql .= implode(',', array_fill(0, $propertyCount, "(?, ?, ?)"));
    $typesStr = str_repeat('ssi' , $propertyCount);

    $sql .= "ON DUPLICATE KEY UPDATE permission = VALUES(permission)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($typesStr, ...$params);

    $headerStr = "Location: /php/allAccounts.php?accountType=" . rawurlencode($accountType) . "&accountName=" . rawurlencode($accountName);
    if($stmt->execute()) {
        if($stmt->affected_rows > 0) {
            $headerStr .= "&editPropertiesSuccess=" . rawurlencode('Account properties updated successfully.');
        } else {
            $headerStr .= "&editPropertiesError=" . rawurlencode('No changes were made.');
        }
    } else {
        $headerStr .= "&editPropertiesError=" . rawurlencode('Failed to update account properties.');
    }
    header($headerStr);
}

$conn->close();

?>