<?php
$conn = new mysqli("localhost", "cwiltool_checkins", "Cwil2622!", "cwiltool_checkins");

if ($conn->connect_errno) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>