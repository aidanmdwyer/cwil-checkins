<?php
$conn = new mysqli("localhost", "cwiltoo1_checkins", "Cwil2622!", "cwiltoo1_checkins");

if ($conn->connect_errno) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>