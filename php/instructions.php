<?php
include 'login.php';
include 'accountProperties.php';

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
    <img src="../imgs/logo.png" style="min-width: 400px; width: 30%;">
    <h1>Check-in App Instructions</h1>
    <p>Please refer to these instructions on how to use the City Wide Check-ins App as an <?php echo $accountType ?>.</p>
</header>
<main>
    <?php if(accountProperties("Home Page")) { ?>
        <h2>Home Page</h2>
        <p>Home Page!!!</p>
    <?php } ?>
</main>
</body>

<style>
    header {
        display: flex;
        flex-direction: column;
        gap: 15px;
        align-items: center;
    }
    header h1 {
        margin: 0;
        padding: 0;
    }
    header p {
        font-size: 1.5rem;
    }

    main {
        display: flex;
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    h1 {
        font-family: Tahoma, sans-serif;
        margin: 1rem 0 2rem 0;
        padding: 0;
    }
    h2 {
        font-family: Tahoma, sans-serif;
        margin: 1rem 0 2rem 0;
        padding: 0;
    }
    h3 {
        font-family: Tahoma, sans-serif;
        margin: 1rem 0 2rem 0;
        padding: 0;
    }
    p {
        font-family: "Times New Roman", times, serif;
        font-size: 1.2rem;
        margin: 0;
        padding: 0;
    }
</style>
</html>
