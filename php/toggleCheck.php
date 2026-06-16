<?php
include 'accountProperties.php';

if (!accountProperties('Toggle Check-ins')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to perform this action.');
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $checked = $_POST['checked'];
    $insChecked = 1 - (int)$checked;

    require_once 'db.php';

    if ($insChecked === 1) {
        $chicagoTime = new DateTime('now', new DateTimeZone('America/Chicago'));
        $formattedTime = $chicagoTime->format('Y-m-d H:i:s');

        $stmt = $conn->prepare("UPDATE buildings SET checked = ?, checkedTime = ? WHERE name = ?");
        $stmt->bind_param("iss", $insChecked, $formattedTime, $name);
    } else {
        $null = null;
        $stmt = $conn->prepare("UPDATE buildings SET checked = ?, checkedTime = ? WHERE name = ?");
        $stmt->bind_param("iss", $insChecked, $null, $name);
    }

    if ($stmt->execute()) {
        echo "Update successful";
    } else {
        http_response_code(500);
        echo "Update failed";
    }

    $conn->query("UPDATE sse_signal SET version = version + 1 WHERE id = 1");

    $stmt->close();
    $conn->close();
}
?>
