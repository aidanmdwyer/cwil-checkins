<?php
require_once 'db.php';

// run query
$result = $conn->query("SELECT version FROM sse_signal WHERE id = 1");

if ($result && $row = $result->fetch_assoc()) {
    echo $row['version'];
} else {
    echo 0; // fallback
}

$conn->close();
?>