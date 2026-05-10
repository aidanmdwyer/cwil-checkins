<?php include 'requireKey.php'; isKeyValid();

require_once 'db.php';

$sql = "SELECT name FROM contractors";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$contractors = [];
while ($row = $result->fetch_assoc()) {
    $contractors[] = $row['name'];
}

echo json_encode($contractors);

$conn->close();
?>