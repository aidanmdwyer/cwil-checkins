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
        <h3>Closet Audits <a href="../php/instructions.php#Closet%20Audits" target="_blank"><img src="../imgs/helpIconWhite.png" alt="help" style="width: 15px; height: 15px;"></a></h3>
        <div>
            <button onclick="window.location.href = '/index.php?logout=logout';" class="big">Logout</button>
            <div style="display: inline-block; vertical-align: middle; line-height: 90%;">
                <span style="font-size: 12px; margin: 0;">Logged in as:<br><?php echo htmlspecialchars($_SESSION['username'])?></span>
            </div>
        </div>
    </header>

    <main>
        <div id="tableMenu" style="display: none; position: fixed; top: 0; left: 0; right: 0; border-bottom: 2px solid black; align-items: center; padding: 15px; background-color: lightgrey; z-index: 10; overflow-x: auto;">
            <div style="margin-right: 20px;">
                <a href="/index.php"><img src="/imgs/logoSmall.png" style="width: 100px;"></a>
            </div>
            <div style="display: flex; justify-content: space-between; width: 100%; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="vr"></div>
                    <label style="display: flex; flex-direction: column;">
                        Filter Building Name
                        <select id="filterBuildingName" name="filterBuildingName"></select>
                    </label>
                    <div class="vr"></div>
                    <label style="display: flex; flex-direction: column;">
                        Filter Auditor
                        <select id="filterAuditor" name="filterAuditor"></select>
                    </label>
                    <div class="vr"></div>
                    <label style="display: flex; flex-direction: column;">
                        Filter IC
                        <select id="filterIC" name="filterIC"></select>
                    </label>
                    <div class="vr"></div>
                </div>
            </div>
        </div>
        <div class="splitBox">
            <div class="left">
                <div id="auditList"></div>
            </div>
            <div class="right"></div>
        </div>
    </main>
</body>

<style>
    html, body {
        width: 100%;
        height: 100%;
    }

    .splitBox {
        display: flex;
        flex-direction: row;
        gap: 0;

        width: 100%;
        height: 100%;
    }
    .splitBox .left {
        flex: 1;
        height: 100%;
    }
    .splitBox .right {
        flex: 2;
        height: 100%;
    }

    #auditList {
        
    }
    #auditList div {

    }
</style>

<script src="/js/accessKey.js"></script>
<script src="/js/adjustMainMargin.js"></script>
<script src="/js/setFiltersAudit.js"></script>
</html>