<?php
include 'login.php';
include 'accountProperties.php';

if (!accountProperties('Import Page')) {
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
    <title>Import Buildings</title>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/x-icon" href="/imgs/favicon.png">
    <link rel="manifest" href="/manifest.json">
</head>
<body>

<header>
    <button onclick="window.location.href='../index.php'" class="big"><span style="font-size: 20px;">&#8592</span> Back to Home</button>
    <h3>Import</h3>
    <div>
        <button onclick="window.location.href = '/index.php?logout=logout';" class="big">Logout</button>
        <div style="display: inline-block; vertical-align: middle; line-height: 90%;">
            <span style="font-size: 12px; margin: 0;">Logged in as:<br><?php echo htmlspecialchars($_SESSION['username'])?></span>
        </div>
    </div>
</header>
<main>
    <div class="contentContainer">
        <div class="card">
            <h2>Import Buildings</h2>
            <p>Upload a ".csv" file with building information.</p>
            <input id="csvFileInput" type="file" accept=".csv"><br><br>

            <button id="previewImport" class="big" style="display: none;" onclick="showImport(false)">Preview Import</button>
            <br>

            <div id="summaryTitle" style="display: none;"></div>
            <div id="numRowsDiv" style="display: none;"></div>
            <div id="numSuccessesDiv" style="display: none;"></div>
            <div id="numExistDiv" style="display: none;"></div>
            <div id="numUpdatedDiv" style="display: none;"></div>
            <div id="missingManagers" style="display: none;"></div>
            <div id="missingIcs" style="display: none;"></div>
            <div id="dayErrors" style="display: none;"></div>
            <div id="errorCount" style="display: none;"></div>

            <button id="commitImport" class="big" style="display: none;" onclick="showImport(true)">Commit Import</button><br><br>

            <table id="importTable"></table>
        </div>
    </div>
</main>

<script src="/js/loadElementVariables.js"></script>
<script src="/js/importBuildings.js"></script>
<script src="/js/adjustMainMargin.js"></script>
<script src="/js/encodeHTML.js"></script>

</body>
</html>