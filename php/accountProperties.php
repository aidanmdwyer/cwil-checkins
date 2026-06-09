<?php
session_start();

$accountType = $_SESSION['accountType'] ?? '';

$accountProperties = [
    'accountType' => $accountType,
    'developer' => [
        'Home Page',
        'Add Building Page',
        'Contractors Page',
        'Managers Page',
        'Archives Page',
        'Import Page',
        'Accounts Page',

        'Select/Edit Multiple Buildings',
        'See Building Name',
        'See Manager',
        'See IC',
        'See Check-in Status',
        'See Check-in Time',
        'Can Toggle Check-ins',
        'See Days',
        'Print QR',
        'Edit Buildings',
        'Access Inactive Buildings',

        'Search Building Name',
        'Filter Manager',
        'Filter IC',
        'Show Today Only',
        'Export Buildings'
    ],
    'admin' => [
        'Home Page',
        'Add Building Page',
        'Contractors Page',
        'Managers Page',
        'Archives Page',
        'Accounts Page',

        'Select/Edit Multiple Buildings',
        'See Building Name',
        'See Manager',
        'See IC',
        'See Check-in Status',
        'See Check-in Time',
        'Can Toggle Check-ins',
        'See Days',
        'Print QR',
        'Edit Buildings',
        'Access Inactive Buildings',

        'Search Building Name',
        'Filter Manager',
        'Filter IC',
        'Show Today Only',
        'Export Buildings'
    ],
    'manager' => [
        'Home Page',
        'Archives Page',

        'See Building Name',
        'See Manager',
        'See IC',
        'See Check-in Status',
        'See Check-in Time',
        'See Days',
        'Print QR',

        'Search Building Name',
        'Filter Manager',
        'Filter IC',
        'Show Today Only',
        'Export Buildings'
    ],
    'contractor' => [
        'Home Page',
        'Archives Page',

        'See Building Name',
        'See Manager',
        'See Check-in Status',
        'See Check-in Time',
        'Can Toggle Check-ins',
        'See Days',

        'Search Building Name',
        'Show Today Only',
        'Export Buildings'
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