<?php
session_start();

$accountType = $_SESSION['accountType'] ?? '';

$accountProperties = [
    'accountType' => $accountType,
    'developer' => [
        'Home',
        'Add Building',
        'Contractors',
        'Managers',
        'Check-In Archives (right)',
        'Import Buildings',
        'All Accounts',

        'Filter Manager',
        'Filter IC',
        'Show Inactive',

        'Select',
        'Name',
        'Manager',
        'IC',
        'Checked',
        'Time Checked',
        'Days',
        'QR',
        'Edit',
        'Edit Note'
    ],
    'admin' => [
        'Home',
        'Add Building',
        'Contractors',
        'Managers',
        'Check-In Archives (right)',
        'All Accounts',

        'Filter Manager',
        'Filter IC',
        'Show Inactive',

        'Select',
        'Name',
        'Manager',
        'IC',
        'Checked',
        'Time Checked',
        'Days',
        'QR',
        'Edit',
        'Edit Note'
    ],
    'manager' => [
        'Home',
        'Check-In Archives (left)',

        'Filter Manager',
        'Filter IC',

        'Name',
        'Manager',
        'IC',
        'Checked View Only',
        'Time Checked',
        'Days',
        'QR',
        'Edit Note'
    ],
    'contractor' => [
        'Home',
        'Check-In Archives (left)',

        'Name',
        'Manager',
        'Checked',
        'Time Checked',
        'Days',
        'View Note'
    ],
    '' => []
];

// --- Dual Mode: JSON API or Inline PHP ---

// If the request has header Accept: application/json, return JSON
if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    header('Content-Type: application/json');
    echo json_encode($accountProperties[$accountType]);
    exit;
}

// Else, allow this file to be included and used in PHP
function accountProperties($property) {
    global $accountProperties;
    global $accountType;
    if($property === 'get') {
        return $accountProperties[$accountType];
    } else {
        return in_array($property, $accountProperties[$accountType]);
    }
}
?>