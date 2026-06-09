<?php
include 'accountProperties.php';

if (!accountProperties('Accounts Page')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to perform this action.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteName'])) {
    $name = htmlspecialchars($_POST['deleteName']);
    $nameRaw = $_POST['deleteName'];

    require_once 'db.php';

    $stmt = $conn->prepare("DELETE FROM users WHERE username = ?");
    $stmt->bind_param("s", $nameRaw);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: /php/allAccounts.php?deleted=" . urlencode($name));
        exit;
    } else {
        $errorMsg = 'Error deleting user: "' . $name;
        header("Location: /php/allAccounts.php?error=" . urlencode($errorMsg));
        exit();
    }
}
?>