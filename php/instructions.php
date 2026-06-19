<?php
include 'login.php';
include 'accountProperties.php';

$accountProperties = $_SESSION['accountProperties'];
$accountType = $_SESSION['accountType'];
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Instructions</title>
</head>
<body>
<header>
    <img src="../imgs/logo.png">
    <h1>Check-in App Instructions</h1>
    <p>Please refer to these instructions on how to use the City Wide Check-ins App as an <?php echo $accountType ?>.</p>
</header>
</body>

<style>
    header {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
</style>
</html>
