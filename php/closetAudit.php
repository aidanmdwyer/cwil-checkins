<?php
include 'login.php';
include 'accountProperties.php';

if (!accountProperties('Closet Audit')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to access this page.');
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Closet Audit</title>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/x-icon" href="/imgs/favicon.png">
    <link rel="manifest" href="/manifest.json">
</head>
<body>
    <form>
        <label>
            Enter:
            <input type="checkbox">
        </label>
    </form>
</body>
<style>

</style>
</html>