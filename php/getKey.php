<?php
session_start();

// Require login before issuing token
if (!isset($_SESSION['username'])) {
    http_response_code(403);
    die("Unauthorized");
}

require 'requireKey.php';
header('Content-Type: application/json');
echo json_encode(['key' => issueJwt("anon", 900)]); // 15 min token
?>