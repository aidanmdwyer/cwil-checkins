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
    <title>Closet Audits</title>
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
                        <select id="filterBuildingName" name="filterBuildingName">
                            <option>---</option>
                        </select>
                    </label>
                    <div class="vr"></div>
                    <label style="display: flex; flex-direction: column;">
                        Filter Auditor
                        <select id="filterAuditor" name="filterAuditor">
                            <option>---</option>
                        </select>
                    </label>
                    <div class="vr"></div>
                    <label style="display: flex; flex-direction: column;">
                        Filter IC
                        <select id="filterIC" name="filterIC">
                            <option>---</option>
                        </select>
                    </label>
                    <div class="vr"></div>
                </div>
            </div>
        </div>
        <div class="splitBox">
            <div class="left">
                <div id="auditList"></div>
            </div>
            <div class="right">
                <div id="noAuditSelectedText"><h1>Select an audit to view.</h1></div>
                <div id="displayAudit">
                    <h1>
                        Closet Audit -
                        <br><span id="displayBuildingName"></span>
                    </h1>

                    <div class='hr'></div>

                    <h3>Date: <span id="displayDate"></span></h3><br>
                    <h3>Auditor: <span id="displayAuditor"></span></h3><br>
                    <h3>Contractor: <span id="displayIC"></span></h3><br>

                    <?php
                    $auditItems = [
                        "Closet Condition" => [
                            "Closet door secured and functioning properly",
                            "Closet is clean and free of debris",
                            "Floors are swept and free of spills",
                            "Shelving is clean and organized",
                            "No unnecessary personal items stored in closet",
                            "QR Code properly stored in closet"
                        ],
                        "Chemical Storage & Safety" => [
                            "All chemicals properly labeled",
                            "SDS (Safety Data Sheets) available on-site",
                            "Chemicals stored upright and secured",
                            "No leaking or damaged containers",
                            "Chemicals separated appropriately",
                            "Spray bottles labeled with contents"
                        ],
                        "Equipment Condition" => [
                            "Vacuum clean and operational",
                            "Mop and bucket clean and in good condition",
                            "Brooms and dustpans stored properly",
                            "Extension cords properly wrapped and stored",
                            "Equipment free from excessive wear or damage",
                            "Equipment stored neatly and safely"
                        ],
                        "Inventory & Supplies" => [
                            "Brut on cart with apron",
                            "Minimum 2 Mop Heads",
                            "Vacuum Cleaner",
                            "Colored microfibers",
                            "Dusting Tool/Dusting Mop",
                            "Mop Bucket"
                        ],
                        "Compliance & Professional Standards" => [
                            "Company-approved products being used",
                            "No unauthorized chemicals present",
                            "PPE available and accessible",
                            "PPE being used appropriately",
                            "Closet reflects company standards"
                        ],
                    ];

                    echo "<div class='hr'></div>";
                    foreach ($auditItems as $section => $list) {
                        echo "<h2>$section</h2>";
                        echo "<table id='auditTable'>";
                        foreach ($list as $item) {
                            echo "
                            <tr>
                                <td class='valueBox'>
                                    <div id='" . $item . "'>$item</div>
                                </td>
                                <td class='commentBox'>
                                    <div id='" . $item . "-comment" . "'></div>
                                </td>
                            </tr>";
                        }
                        echo "</table>";
                        echo "<br><div class='hr'></div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>
</body>

<style>
    html, body {
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    body {
        display: flex;
        flex-direction: column;
    }

    main {
        flex: 1;
        min-height: 0;
    }

    h1 {
        text-align: center;
    }
    h3 {
        margin: 0;
        padding: 0;
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
        overflow-y: scroll;
    }
    .splitBox .right {
        flex: 2;
        height: 100%;
        overflow-y: scroll;
    }

    #auditList {
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-height: 100%;
        padding: 10px;
        border-right: 2px solid black;
        box-sizing: border-box;
    }
    #auditList > div {
        background: white;
        border-radius: 5px;
        display: flex;
        flex-direction: column;
        padding: 10px;
        box-sizing: border-box;
        gap: 10px;
    }
    #auditList > div > div {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
    }
    #auditList > div > div > h3, #auditList > div > div > p {
        margin: 0;
        padding: 0;
    }

    #displayAudit {
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;

        padding: 0 30px 200px 30px;
        box-sizing: border-box;
    }
    .commentBox {
        width: max(20%, 250px);
        padding: 5px 10px;
        box-sizing: border-box;
    }
    #auditTable div.checked::before {
        content: ✅
    }
    #auditTable div.unchecked::before {
        content: ❌
    }
</style>

<script src="/js/accessKey.js"></script>
<script src="/js/adjustMainMargin.js"></script>
<script src="/js/setFiltersAudit.js"></script>
<script>
    accessKeyReady.then(() => {
        loadAuditFilterData();
        buildAuditList();
    });
</script>
</html>