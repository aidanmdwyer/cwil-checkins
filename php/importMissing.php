<?php
include 'requireKey.php'; isKeyValid();

include 'accountProperties.php';

if (!accountProperties('Import Buildings')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to perform this action.');
}

require_once 'db.php';

header('Content-Type: application/json');

// --- read JSON input ---
$payload = file_get_contents("php://input");
$data = json_decode($payload, true);

if (!isset($data['type'], $data['items']) || !is_array($data['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$type  = $data['type'];
$items = $data['items'];

// only allow known table names
if ($type !== 'managers' && $type !== 'contractors') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid type']);
    exit;
}

try {
    // prepare insert query with mysqli style placeholder (?)
    $stmt = $conn->prepare("INSERT IGNORE INTO `$type` (name) VALUES (?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $count = 0;
    foreach ($items as $name) {
        $name = trim((string)$name);
        if ($name === '') continue;

        $stmt->bind_param("s", $name);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $count++;
        }
    }
    $stmt->close();

    echo json_encode(['success' => true, 'inserted' => $count]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
