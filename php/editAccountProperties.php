<?php
session_start();

include 'login.php';
include 'accountProperties.php';

if (!accountProperties('Accounts Page')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to access this page.');
}

unset($_SESSION['accountProperties']);

// Connect to database
require_once 'db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['properties']) && isset($_POST['accountName']) && isset($_POST['accountType'])) {

    $accountName = rawurldecode($_POST['accountName']);
    $accountType = rawurldecode($_POST['accountType']);

    $headerStr = "Location: /php/allAccounts.php?accountType=" . rawurlencode($accountType) . "&accountName=" . rawurlencode($accountName);

    $doInsert = true;

    if($_POST['resetToDefaults'] === 'true') {
        $doInsert = false;
    } else if ($_POST['accountName'] !== 'default') {
        $doInsert = false;

        $defaultStmt = $conn->prepare("SELECT property, permission FROM account_properties WHERE accountName = ?");
        $defaultStmt->bind_param("s", $accountType);
        $defaultStmt->execute();
        $defaultResult = $defaultStmt->get_result();

        $defaultPermissions = [];
        while ($row = $defaultResult->fetch_assoc()) {
            $defaultPermissions[$row['property']] = (int)$row['permission'];
        }

        foreach ($_POST['properties'] as $property => $permission) {
            $property = rawurldecode($property);
            $permission = (int)$permission;

            if (!isset($defaultPermissions[$property]) || $defaultPermissions[$property] !== $permission) {
                $doInsert = true;
                break;
            }
        }
    }

    if($doInsert) {
        if ($_POST['accountName'] === 'default') {
            $insName = $accountType;
        } else {
            $insName = $accountName;
        }

        $insertParams = [];

        $insertSql = "INSERT INTO account_properties (accountName, property, permission) VALUES ";

        $propertyCount = 0;
        foreach ($_POST['properties'] as $property => $permission) {
            $propertyCount++;
            $insertParams[] = $insName;
            $insertParams[] = rawurldecode($property);
            $insertParams[] = (int)$permission;
        }
        $insertSql .= implode(',', array_fill(0, $propertyCount, "(?, ?, ?)"));
        $insertTypes = str_repeat('ssi', $propertyCount);

        $insertSql .= "ON DUPLICATE KEY UPDATE permission = VALUES(permission)";

        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param($insertTypes, ...$insertParams);

        if ($insertStmt->execute()) {
            if ($insertStmt->affected_rows > 0) {
                $headerStr .= "&editPropertiesSuccess=" . rawurlencode('Account permissions for ' . $insName . ' were updated successfully.');
            } else {
                $headerStr .= "&editPropertiesError=" . rawurlencode('No changes were made to the account permissions for ' . $insName . '.');
            }
        } else {
            $headerStr .= "&editPropertiesError=" . rawurlencode('Failed to update account permissions for ' . $insName . '.');
        }
    } else {
        $existingStmt = $conn->prepare("SELECT 1 FROM account_properties WHERE accountName = ?");
        $existingStmt->bind_param("s", $accountName);
        $existingStmt->execute();
        $existingResult = $existingStmt->get_result();

        if ($existingResult->num_rows > 0) {
            $deleteStmt = $conn->prepare("DELETE FROM account_properties WHERE accountName = ?");
            $deleteStmt->bind_param("s", $accountName);
            if ($deleteStmt->execute()) {
                $headerStr .= "&editPropertiesSuccess=" . rawurlencode('Account permissions for ' . $accountName . ' set to ' . $accountType . ' defaults.');
            } else {
                $headerStr .= "&editPropertiesError=" . rawurlencode('SQL Error: No changes were made to the account permissions for ' . $accountName . '.');
            }
        } else {
            $headerStr .= "&editPropertiesError=" . rawurlencode('No changes were made, the account permissions for ' . $accountName . ' remain the ' . $accountType . ' defaults.');
        }
    }
    header($headerStr);
}

$conn->close();

?>