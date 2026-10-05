<?php
include 'login.php';
include 'accountProperties.php';

if (!accountProperties('Closet Audits Page')) {
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
    <header>
        <button onclick="window.location.href='../index.php'" class="big">&#8592 Back to Home</button>
        <h3>Closet Audits <a href="../php/instructions.php#Adding%20New%20Buildings" target="_blank"><img src="../imgs/helpIconWhite.png" alt="help" style="width: 15px; height: 15px;"></a></h3>
        <div>
            <button onclick="window.location.href = '/index.php?logout=logout';" class="big">Logout</button>
            <div style="display: inline-block; vertical-align: middle; line-height: 90%;">
                <span style="font-size: 12px; margin: 0;">Logged in as:<br><?php echo htmlspecialchars($_SESSION['username'])?></span>
            </div>
        </div>
    </header>
    <main>
        <div class="contentContainer">
        </div>
    </main>
</body>
</html>