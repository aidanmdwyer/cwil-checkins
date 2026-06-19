<?php
include 'php/login.php';
include 'php/accountProperties.php';

if (!accountProperties('Home Page')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to access this page.');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.7">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title id="title">Buildings</title>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/x-icon" href="/imgs/favicon.png">
    <link rel="manifest" href="/manifest.json">
    <script src="/modules/xlsx.full.min.js"></script>
</head>
<body>
<header>
    <div>
        <button onclick="window.location.href='./php/addBuilding.php'" class="big" style="display: <?php echo accountProperties('Add Building Page') ? '' : 'none';?>;">Add Building</button>
        <button onclick="window.location.href='./php/addContractor.php'" class="big" style="display: <?php echo accountProperties('Contractors Page') ? '' : 'none';?>;">Contractors</button>
        <button onclick="window.location.href='./php/addManager.php'" class="big" style="display: <?php echo accountProperties('Managers Page') ? '' : 'none';?>;">Managers</button>
    </div>
    <h3>City Wide Check-Ins <a href="./php/instructions.php" target="_blank"><img src="./imgs/help_icon.png" alt="help" style="width: 15px; height: 15px;"></a></h3>
    <div>
        <button onclick="window.location.href='./php/checkInArchives.php'" class="big" style="display: <?php echo accountProperties('Archives Page') ? '' : 'none';?>;">Archives</button>
        <button onclick="window.location.href='./php/importBuildings.php'" class="big" style="display: <?php echo accountProperties('Import Page') ? '' : 'none';?>;">Import</button>
        <button onclick="window.location.href='./php/allAccounts.php'" class="big" style="display: <?php echo accountProperties('Accounts Page') ? '' : 'none';?>;">Accounts</button>
        <div style="display: inline-block;">
            <button onclick="window.location.href = '/index.php?logout=logout';" class="big">Logout</button>
            <div style="display: inline-block; vertical-align: middle; line-height: 90%;">
                <span style="font-size: 12px; margin: 0; text-align: left;">Logged in as:<br><?php echo htmlspecialchars($_SESSION['username'])?></span>
            </div>
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
                <div class="vr" style="<?php echo accountProperties('Search Building Name') ? 'display: block' : 'display: none';?>;"></div>
                <form id="searchBuildingsForm" style="<?php echo accountProperties('Search Building Name') ? 'display: block' : 'display: none';?>;">
                    <label style="display: flex; flex-direction: column;">
                        Search Building Name
                        <span style="display: flex; flex-direction: row; gap: 5px; align-items: center;">
                            <input type="text" id="searchBuildings" name="searchBuildings"/>
                            <button type="submit" class="big">Enter</button>
                        </span>
                    </label>
                </form>
                <div class="vr" style="<?php echo accountProperties('Filter Manager') ? 'display: block' : 'display: none';?>;"></div>
                <label style="<?php echo accountProperties('Filter Manager') ? 'display: flex;' : 'display: none';?>; flex-direction: column;">
                    Filter Manager
                    <select id="filterManager" name="filterManager"></select>
                </label>
                <div class="vr" style="<?php echo accountProperties('Filter IC') ? 'display: block' : 'display: none';?>;"></div>
                <label style="<?php echo accountProperties('Filter IC') ? 'display: flex' : 'display: none';?>; flex-direction: column;">
                    Filter IC
                    <select id="filterIC" name="filterIC"></select>
                </label>
                <div class="vr" style="<?php echo accountProperties('Filter Today Only') ? 'display: block' : 'display: none';?>;"></div>
                <label style="<?php echo accountProperties('Filter Today Only') ? 'display: flex' : 'display: none';?>; flex-direction: column; align-items: center; white-space: nowrap;">
                    Today Only
                    <input id="todayOnly" type="checkbox" name="todayOnly">
                </label>
                <div class="vr" style="<?php echo accountProperties('Access Inactive Buildings') ? 'display: block' : 'display: none';?>;"></div>
                <div style="<?php echo accountProperties('Access Inactive Buildings') ? 'display: flex' : 'display: none';?>; align-items: center; gap: 15px;">
                    <div style="text-align: center;">Show<br>Inactive</div>
                    <label class="switch">
                        <input id="showActive" type="checkbox" checked>
                        <span class="slider round"></span>
                    </label>
                    <div style="text-align: center;">Show<br>Active</div>
                </div>
                <div class="vr"></div>
                <button id="refreshButton" class="big" style="display: none;">Refresh</button>
            </div>
            <div style="<?php echo accountProperties('Export Buildings') ? 'display: flex' : 'display: none';?>; align-items: center; gap: 15px;">
                <button id="mainExportButton" class="big" onclick="exportBuildingsSheet('buildingsTable', new Date().toISOString().slice(0,10) + '_buildings.xlsx')">Export</button>
            </div>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start;">
        <!--        Table-->
        <div id="tableContainer" style="display: inline-block; margin: 0 20px;">
            <h2 id="inactiveText" style="color: #bf3232; margin-top: 10px; display: none;">***INACTIVE BUILDINGS***</h2>
            <form id="tableSelectedForm" method="POST" action="php/handleSelect.php">
                <table id="buildingsTable" style="display: none;"></table>
            </form>
            <button id="loadAll" class="big" style="display: none;">Load All</button>
            <div style="height: 200px;"></div>
        </div>

        <!--        Edit form-->
        <div id="editForm" style="display: none; position: sticky; padding-right: 10px;">
            <h1>Edit Building</h1>
            <p id="editName"></p>
            <form method="POST" action="php/editRow.php">
                <input type="hidden" id="editNameInput" name="editName">

                <label for="Manager">Manager</label><br>
                <select id="Manager" name="manager" style="width: 294px"></select><br><br>

                <label for="Contractor">Contractor</label><br>
                <input type="text" id="Contractor" name="contractor" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" style="width: 294px"><br>
                <div id="contractorSuggestions" class="contractorSuggestions"></div><br>

                <div id="dayChecks" style="display: flex; gap: 10px; flex-direction: column;">
                    <div style="display: flex; gap: 16px;">
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            M
                            <input id="monday" type="checkbox" name="monday" onclick="handleDayCheck(this.checked)">
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            T
                            <input id="tuesday" type="checkbox" name="tuesday" onclick="handleDayCheck(this.checked)">
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            W
                            <input id="wednesday" type="checkbox" name="wednesday" onclick="handleDayCheck(this.checked)">
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            Th
                            <input id="thursday" type="checkbox" name="thursday" onclick="handleDayCheck(this.checked)">
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            F
                            <input id="friday" type="checkbox" name="friday" onclick="handleDayCheck(this.checked)">
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            Sat
                            <input id="saturday" type="checkbox" name="saturday" onclick="handleDayCheck(this.checked)">
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            Sun
                            <input id="sunday" type="checkbox" name="sunday" onclick="handleDayCheck(this.checked)">
                        </label>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            Check MWF
                            <input id="MWFCheckbox" type="checkbox" onclick="handleDayCheckMWF(this.checked)">
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            Check M-F
                            <input id="MFCheckbox" type="checkbox" onclick="handleDayCheckMF(this.checked)">
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            Check All
                            <input id="allDaysCheckbox" type="checkbox" onclick="handleDayCheckAll(this.checked)">
                        </label>
                    </div>

                </div><br>

                <button type="button" onclick="cancel()" class="big">Cancel</button>
                <button type="submit" class="big">Submit</button>

                <div id="resultMessage" style="margin-top: 10px;"></div>
            </form>
        </div>

        <!--        QR preview-->
        <div id="qrForm" style="display: none; text-align: center; width: 300px; position: sticky; padding-right: 10px;">
            <h1>Check-In QR Code</h1>
            <p id="qrPreviewTitle" style="color: #2c2c24; font-size: 25px; font-weight: bold;"></p>
            <p id="qrPreviewIC" style="color: #da3f42; font-size: 20px; font-weight: bold;"></p>
            <div style="width: 300px; height: 300px; display: flex; background-color: white; text-align: center; justify-content: center; align-items: center;">
                <div id="qrcodePreview"></div>
            </div><br>
            <div style="text-align: center;">
                <button class="big" onclick="cancel()">Cancel</button>
                <button class="big" onclick="printQR()">Print</button>
            </div>
        </div>

        <!--        QR print template-->
        <div id="qrTemplate" style="display: none; height: 4.25in; background-color: white; text-align: center; justify-content: space-evenly; align-items: center; padding: 0.25in 0">
            <div style="display: inline-block; text-align: center; justify-content: center; width: 340px;">
                <img src="/imgs/logo.png" style="width: 3.5in; margin-bottom: 0.25in">
                <div id="printBuildingName" style="font-size: 30px; color: #2c2c24; font-weight: bold;"></div><br>
            </div>
            <div style="display: flex; flex-direction: column; align-items: center;">
                <div style="font-size: 20px;">Scan this QR code to check in.</div>
                <div style="font-size: 16px;">Escanee este c&#243digo QR para hacer check-in.</div>
                <div style="font-size: 16px;">Zeskanuj kod QR, &#380eby si&#281 zameldowa&#263.</div>
                <div id="qrcode" style="width: 2.75in; height: 2.75in; margin-top: 0.15in"></div>
            </div>
        </div>

        <iframe id="printFrame" style="display: none;"></iframe>

        <!--            Print Overlay-->
        <div id="printAllOverlay" style="
                position: fixed;
                top: 0; left: 0;
                width: 100%; height: 100%;
                background: rgba(0,0,0,0.6);
                color: white;
                font-size: 32px;
                display: none;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                z-index: 9999;
            ">
            <div id="printProgressText">Loading</div>
            <progress id="printProgress" value="0" max="100"></progress>
        </div>

        <!--        Select page-->
        <div id="selectSubmits" style="display: none; width: 350px; position: sticky; padding-right: 10px;">
            <h1 id="selectedBuildings"></h1>

            <div style="border-bottom: 2px solid black; padding-bottom: 20px;">
                <?php
                if(accountProperties("Print QR")) {
                ?>
                <button type="button" form="tableSelectedForm" class="big" style="background-color: blue; color: white;" onclick="printAll()">Print Selected</button>
                <?php }
                if(accountProperties("Delete Buildings")) {
                ?>
                <button type="button" form="tableSelectedForm" class="big" style="background-color: darkred; color: white;" onclick="showDeletePopup(event)">Delete Selected</button>
                <?php } ?>
            </div>

            <?php
            if(accountProperties("Edit Buildings")) {
            ?>
            <div style="border-bottom: 2px solid black; padding-bottom: 20px;">
                <h3>Edit All</h3>

                <div style="margin-bottom: 10px;">
                    <button type="button" class="big" onclick="changeManager(event)">Change Manager</button>
                    <button type="button" class="big" onclick="changeIC(event)">Change IC</button>
                </div>

                <div style="margin-bottom: 10px;">
                    <div id="changeManagerDiv" class="card" style="display: none; margin: 10px 0 10px 0;">
                        <label for="changeManager">Change Manager</label><br>
                        <select id="changeManager" form="tableSelectedForm" name="changeManager"></select>
                        <br><br>
                        <button type="button" form="tableSelectedForm" class="big" onclick="editAll(event, 'changeManager')">Submit</button>
                        <button type="button" form="tableSelectedForm" class="big" onclick="event.target.parentElement.style.display = 'none'">Close</button>
                        <br>
                    </div>
                    <div id="changeICDiv" class="card" style="display: none; margin: 10px 0 10px 0;">
                        <label for="changeIC">Change IC</label><br>
                        <input id="changeIC" form="tableSelectedForm" name="changeIC"><br>
                        <div id="changeICSuggestions" class="contractorSuggestions"></div>
                        <br>
                        <button type="button" form="tableSelectedForm" class="big" onclick="editAll(event, 'changeIC')">Submit</button>
                        <button type="button" form="tableSelectedForm" class="big" onclick="event.target.parentElement.style.display = 'none'">Close</button>
                        <br>
                    </div>
                </div>

                <?php
                if(accountProperties("Toggle Check-ins")) {
                ?>
                <div style="margin-bottom: 10px; margin-top: 10px;">
                    <button id="checkAll" type="button" class="big" onclick="editAll(event, 'checkAll')">Check All</button>
                    <button id="uncheckAll" type="button" class="big" onclick="editAll(event, 'uncheckAll')">Uncheck All</button>
                </div>
                <?php } ?>

                <?php
                if(accountProperties("Access Inactive Buildings")) {
                ?>
                <div style="margin-top: 10px;">
                    <button id="deactivateButton" type="button" class="big" onclick="editAll(event, 'deactivate')">Deactivate</button>
                    <button id="activateButton" type="button" class="big" onclick="editAll(event, 'activate')" style="display: none">Activate</button>
                </div>
                <?php } ?>

                <div id="editAllMessage" style="display: none; margin-top: 10px;"></div>
            </div>
            <?php } ?>

            <button type="button" class="big" onclick="cancel()" style="margin-top: 20px;">Cancel</button><br>
            <p style="color: red">actions performed on large selections may take a long time.</p>
        </div>
    </div>
</main>
</body>

<script src="/modules/qrcode.min.js"></script>

<script>
    sessionAccountType = "<?php echo $_SESSION['accountType']?>";
    sessionUsername = "<?php echo $_SESSION['username']?>";
</script>


<script src="/js/loadElementVariables.js"></script>
<script src="/js/editFormListener.js"></script>
<script src="/js/editAll.js"></script>
<script src="/js/encodeHTML.js"></script>
<script src="/js/buildTable.js"></script>
<script src="/js/fillSelectMenu.js"></script>
<script>
    accessKeyReady.then(() => {
        fetch('/php/searchManagers.php?key=' + accessKey)
            .then(response1 => response1.json())
            .then(managerData => {
                fetch('/php/searchContractors.php?key=' + accessKey)
                    .then(response2 => response2.json())
                    .then(icData => {
                        contractorList = icData;

                        fillSelectMenu("filterManager", managerData);
                        fillSelectMenu("filterIC", icData);
                        document.getElementById('filterManager').value = ("<?php echo $_SESSION['accountType']?>" === 'manager') ? "<?php echo $_SESSION['username']?>" : '---';

                        const urlParams = new URLSearchParams(window.location.search);
                        const selectedManager = urlParams.get('manager');
                        const todayOnly = urlParams.has('todayOnly') ? urlParams.get('todayOnly') : '<?php echo accountProperties('Filter Today Only') ? 'true' : 'false'?>';
                        const ic = urlParams.get('ic');
                        if(selectedManager) filterManager.value = selectedManager;
                        if(ic) filterIC.value = ic;
                        document.getElementById('todayOnly').checked = (todayOnly === 'true');
                        buildTable();
                    });
            });
    })
</script>
<script src="/js/handleSidebars.js"></script>
<script src="/js/handleSelect.js"></script>
<script src="/js/handleQR.js"></script>
<script src="/js/toggleCheck.js"></script>
<script src="/js/searchContractors.js"></script>
<script>
    searchContractors('Contractor', 'contractorSuggestions');
</script>
<script src="/js/setFilters.js"></script>
<script src="/js/handleDayChecks.js"></script>
<script src="/js/deletePopup.js"></script>
<script>
    refreshButton.onclick = () => {
        buildTable();
    }
</script>
<script src="/js/adjustMainMargin.js"></script>
<script src="/js/copyAccountLink.js"></script>
<script src="/js/checkSignalVersion.js"></script>
<script src="/js/tableToExcel.js"></script>

</html>