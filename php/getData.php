<?php
session_start();
include 'requireKey.php';
isKeyValid();

require_once 'db.php';

$manager = $_GET['manager'] ?? '';
$ic = $_GET['ic'] ?? '';
$todayOnly = $_GET['todayOnly'] ?? '';
$loadAll = $_GET['loadAll'] ?? '';
$showActive = $_GET['showActive'] ?? '';
$search = $_GET['search'] ?? '';

//Normalize to boolean
$todayOnly = strtolower(trim($todayOnly)) === 'true';
$loadAll = strtolower(trim($loadAll)) === 'true';
$showActive = strtolower(trim($showActive)) === 'true';

$now = new DateTime('now', new DateTimeZone('America/Chicago'));
if((int)$now->format('H') < 3) {
    $now->modify('-1 day');
}
$dayOfWeek = strtolower($now->format('l'));

$conditions = [];
$params = [];
$types = ''; // parameter types for bind_param

// Manager filter
if (!empty($manager) && $manager !== '---') {
    $conditions[] = "manager = ?";
    $params[] = $manager;
    $types .= 's';
}

// IC filter
if (!empty($ic) && $ic !== '---') {
    $conditions[] = "ic = ?";
    $params[] = $ic;
    $types .= 's';
}

// Today only filter
if ($todayOnly) {
    $conditions[] = "$dayOfWeek = 1";
}

// Active filter
if ($showActive) {
    $conditions[] = "active = 1";
} else {
    $conditions[] = "active = 0";
}

// Search filter (parameterized)
if (!empty($search)) {
    $conditions[] = "name LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

// Session-based contractor filter
if ($_SESSION['accountType'] === 'contractor') {
    $conditions[] = "ic = ?";
    $params[] = $_SESSION['username'];
    $types .= 's';
}

// Build SQL query
$sql = "SELECT * FROM buildings";
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY name";
if (!$loadAll) {
    $sql .= " LIMIT 21";
}

// Prepare statement
$stmt = $conn->prepare($sql);

// Bind parameters dynamically if there are any
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Execute and fetch
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

// Handle 'hasMore' logic
$hasMore = false;
if(!$loadAll && count($rows) > 20) {
    $hasMore = true;
    array_pop($rows);
}

// Output
echo json_encode([
    'dayOfWeek' => $dayOfWeek,
    'rows' => $rows,
    'hasMore' => $hasMore
]);
?>