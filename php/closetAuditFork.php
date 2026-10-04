<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Check-in or Closet Audit?</title>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/x-icon" href="/imgs/favicon.png">
    <link rel="manifest" href="/manifest.json">
</head>
<body>
    <button onclick="window.location.href='checkIn.php?bypassFork=true&name=<?=$_GET['name']?>'">Click Here to Check In.</button>
    <button onclick="window.location.href='closetAudit.php'">Click Here for Closet Audit.</button>
</body>
<style>
    html {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
    }
    body {
        width: min(100%, 1000px);
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 0;
    }
    button {
        width: 100%;
        height: 50%;

        box-sizing: border-box;
        padding: 20px;
        margin: 30px 0;

        font-size: 50px;
        font-family: Georgia, serif;
    }
</style>
</html>