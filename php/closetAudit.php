<?php
include 'login.php';
include 'accountProperties.php';

if (!accountProperties('Closet Audit')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to access this page.');
}

$buildingName = $_GET['buildingName'];
if(!$buildingName) {
    http_response_code(404);
    die('Page load failed.');
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
    <h1>Closet Audit -</h1>
    <h2><?= $buildingName?></h2>
    <form>
        <label>
            Enter:
            <input type="checkbox">
        </label>
    </form>
</body>
<style>
    html {
        width: 100%;
        height: 100%;
    }
    body {
        width: 100%;
        margin: 0 auto;

        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: center;
        justify-content: flex-start;
    }
</style>
</html>