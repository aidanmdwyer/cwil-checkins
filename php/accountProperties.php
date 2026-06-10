<?php
session_start();

if(!isset($_SESSION['accountProperties'])){
    // Connect to database
    require_once 'db.php';

    $accountType = $_SESSION['accountType'];
    $username = $_SESSION['username'];

    if ($accountType && $username) {

        if ($accountType === 'developer') {
            $accountProperties = '*';
        } else {
            $accountProperties = [];

            $checkCustomStmt = $conn->prepare("SELECT 1 FROM account_properties WHERE accountName = ?");
            $checkCustomStmt->bind_param("s", $username);
            $checkCustomStmt->execute();
            $checkCustomResult = $checkCustomStmt->get_result();
            if ($checkCustomResult->num_rows > 0) {
                $selectPropertiesName = $username;
            } else {
                $selectPropertiesName = $accountType;
            }

            $selectPropertiesStmt = $conn->prepare("SELECT property, permission FROM account_properties WHERE accountName = ?");
            $selectPropertiesStmt->bind_param("s", $selectPropertiesName);
            $selectPropertiesStmt->execute();
            $selectPropertiesResult = $selectPropertiesStmt->get_result();
            while ($row = $selectPropertiesResult->fetch_assoc()) {
                if ((int)$row['permission'] === 1) {
                    $accountProperties[] = $row['property'];
                }
            }
        }
    }

    $_SESSION['accountProperties'] = $accountProperties;
}

// if the request has header Accept: application/json, return JSON
if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    header('Content-Type: application/json');
    echo json_encode($_SESSION['accountProperties']);
    exit;
}

// otherwise allow this file to be included and used in PHP
function accountProperties($property) {
    if($_SESSION['accountProperties'] === '*') {
        return true;
    }
    return in_array($property, $_SESSION['accountProperties']);
}
?>