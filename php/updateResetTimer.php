<?php
include 'requireKey.php';
isKeyValid();

include 'accountProperties.php';

if (!accountProperties('Contractors')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to perform this action.');
}

header('Content-Type: application/json');

require_once 'db.php';

// --- read JSON input ---
$payload = file_get_contents("php://input");
$data = json_decode($payload, true);

if (!isset($data['username'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No username provided']);
    exit;
}

$username = $conn->real_escape_string($data['username']);

// --- update resetTimer column ---
$sql = "UPDATE users SET resetTimer = NOW() WHERE username = '$username'";
if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true, 'username' => $username, 'resetTimer' => date("Y-m-d H:i:s")]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
