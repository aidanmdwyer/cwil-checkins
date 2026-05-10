<?php include 'requireKey.php'; isKeyValid();

require_once 'db.php';

$sql = "SELECT name FROM managers";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$managers = [];
while ($row = $result->fetch_assoc()) {
    $managers[] = $row['name'];
}

echo json_encode($managers);

$conn->close();
?>