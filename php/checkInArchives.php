<?php
include 'login.php';
include 'accountProperties.php';

if (!accountProperties('Check-In Archives (right)') && !accountProperties('Check-In Archives (left)')) {
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
    <title>Check-In Archives</title>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/x-icon" href="/imgs/favicon.png">
    <link rel="manifest" href="/manifest.json">
    <script src="/modules/xlsx.full.min.js"></script>
</head>
<body>

<header>
    <button onclick="window.location.href='../index.php'" class="big"><span style="font-size: 20px;">&#8592</span> Back to Home</button>
    <h3>Check-In Archives</h3>
    <div>
        <button onclick="window.location.href = '/index.php?logout=logout';" class="big">Logout</button>
        <div style="display: inline-block; vertical-align: middle; line-height: 90%;">
            <span style="font-size: 12px; margin: 0;">Logged in as:<br><?php echo htmlspecialchars($_SESSION['username'])?></span>
        </div>
    </div>
</header>

<main>
    <div id="tableMenu" style="display: none; position: fixed; top: 0; left: 0; right: 0; border-bottom: 2px solid black; align-items: center; padding: 15px; background-color: lightgrey; overflow-x: auto;">
        <div style="margin-right: 30px;">
            <a href="/index.php"><img src="/imgs/logoSmall.png" style="width: 100px;"></a>
        </div>
        <div style="display: flex; justify-content: space-between; width: 100%;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div class="vr"></div>
                <label style="display: flex; flex-direction: column;">
                    Date
                    <input type="date" id="archiveDate" name="archiveDate">
                </label>
                <div id="searchVr" class="vr" style="display: none;"></div>
                <div id="searchBox" style="display: none;">
                    <form id="searchArchivesForm">
                        <label style="display: flex; flex-direction: column;">
                            Search
                            <span style="display: flex; flex-direction: row; gap: 5px; align-items: center;">
                                <input type="text" id="searchArchives" name="searchContractors"/>
                                <button type="submit" class="big">Enter</button>
                            </span>
                        </label>
                    </form>
                </div>
                <div id="filterManagerVr" class="vr" style="display: none;"></div>
                <div id="filterManagerBox" style="display: none;">
                    <label style="display: flex; flex-direction: column;">
                        Filter Manager
                        <select id="filterManager" name="filterManager"></select>
                    </label>
                </div>
                <div id="filterICVr" class="vr" style="display: none;"></div>
                <div id="filterICBox" style="display: none;">
                    <label style="display: flex; flex-direction: column;">
                        Filter IC
                        <select id="filterIC" name="filterIC"></select>
                    </label>
                </div>
            </div>
            <div style="display: flex; align-items: center;">
                <button id="archiveExportButton" class="big" onclick="exportBuildingsSheet('archiveTable', new Date().toISOString().slice(0,10) + '_archive.xlsx')" style="margin-left: 20px; display: none;">Export</button>
            </div>
        </div>
    </div>

    <div class="card">
        <h2 id="archiveText">No Date Selected</h2>
        <div id="tableContainer" style="display: inline-block;">
            <table id="archiveTable" style="display: inline-block;">
            </table>
        </div>
    </div>
</main>
</body>


<script src="/js/loadElementVariables.js"></script>
<script src="/js/encodeHTML.js"></script>
<script src="/js/fillSelectMenu.js"></script>
<script>
    let archiveTable = document.getElementById('archiveTable');
    archiveTable.innerHTML = "Select a date above to see archive.";
    const defaultManagerFilter = ("<?php echo $_SESSION['accountType']?>" === 'manager') ? "<?php echo $_SESSION['username']?>" : '---';

    accessKeyReady.then(() => {
        fetch('/php/searchManagers.php?key=' + accessKey)
            .then(response1 => response1.json())
            .then(managerData => {
                fetch('/php/searchContractors.php?key=' + accessKey)
                    .then(response2 => response2.json())
                    .then(icData => {
                        fillSelectMenu("filterManager", managerData);
                        fillSelectMenu("filterIC", icData);
                        document.getElementById('filterManager').value = defaultManagerFilter;
                    });
            });
    });
</script>
<script>
    let archiveData = [];

    function buildTableArchive(data) {
        let htmlStr = "";

        const managerFilter = document.getElementById('filterManager').value;
        const icFilter = document.getElementById('filterIC').value;
        const filterManagerBox = document.getElementById('filterManagerBox');
        const filterICBox = document.getElementById('filterICBox');
        const searchVr = document.getElementById("searchVr");
        const filterManagerVr = document.getElementById("filterManagerVr");
        const filterICVr = document.getElementById("filterICVr");
        const searchBox = document.getElementById('searchBox');
        const archiveExportButton = document.getElementById('archiveExportButton');

        const inputValue = document.getElementById('archiveDate').value; //"2025-07-08"
        const [year, month, day] = inputValue.split('-');
        const date = new Date(year, month - 1, day); //Note: month is 0-indexed

        const formattedDate = date.toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'});
        document.getElementById('archiveText').innerText = 'Archive: ' + formattedDate;

        archiveTable.style.display = 'inline-block';
        archiveTable.innerHTML = "";

        filterManagerBox.style.display = 'block';
        filterManagerVr.style.display = 'block';
        searchBox.style.display = 'block';
        searchVr.style.display = 'block';
        filterICBox.style.display = "<?php echo accountProperties('Filter IC') ? 'block' : 'none';?>";
        filterICVr.style.display = "<?php echo accountProperties('Filter IC') ? 'block' : 'none';?>";
        archiveExportButton.style.display = 'inline-block';

        if(data.length > 0) {

            let filteredData = [];
            data.forEach(rowData => {
                if ((managerFilter === "---" || managerFilter === rowData['manager']) && (icFilter === "---" || icFilter === rowData['ic'])) {
                    filteredData.push(rowData);
                }
            })
            if (filteredData.length > 0) {
                if ("<?php echo htmlspecialchars($_SESSION['accountType'])?>" === "contractor") {
                    htmlStr = `
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Manager</th>
                                <th>&#9989</th>
                                <th>Time Checked</th>
                            </tr>
                        </thead>
                        <tbody>
                    `;
                } else {
                    htmlStr = `
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Manager</th>
                                <th>IC</th>
                                <th>&#9989</th>
                                <th>Time Checked</th>
                            </tr>
                        </thead>
                        <tbody>
                    `;
                }

                let colorSwitch = false;
                filteredData.forEach(rowData => {

                    let trStr = `<tr class="` + (colorSwitch ? 'odd' : 'even') + `">`;

                    trStr += `<td>${rowData['name']}</td>`;

                    trStr += `<td>${rowData['manager']}</td>`;

                    if ("<?php echo htmlspecialchars($_SESSION['accountType'])?>" !== "contractor") {
                        trStr += `<td>${rowData['ic']}</td>`;
                    }

                    trStr += `<td><span style="font-size: 10px; text-align: center;">${(rowData['checked'] === 0) ? '&#10060' : '&#9989'}</span></td>`;

                    let rawTime = rowData['checkedTime'];
                    if (rawTime) {
                        let date = new Date(rawTime);

                        const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                            "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                        let month = monthNames[date.getMonth()];
                        let day = date.getDate();

                        let hours = date.getHours();
                        let minutes = date.getMinutes();

                        let ampm = hours >= 12 ? 'PM' : 'AM';
                        hours = hours % 12;
                        hours = hours ? hours : 12; //Convert 0 to 12
                        let minutesStr = minutes < 10 ? '0' + minutes : minutes;

                        trStr += `<td style="font-family: 'Arial', sans-serif; text-align: center;">${month} ${day}, ${hours}:${minutesStr} ${ampm}</td>`;
                    } else {
                        trStr += `<td></td>`;
                    }

                    trStr += `</tr>`

                    htmlStr += trStr;

                    colorSwitch = !colorSwitch;
                });
                htmlStr += `</tbody>`;
            } else {
                filterManagerBox.style.display = 'block';
                filterManagerVr.style.display = 'block';
                searchBox.style.display = 'block';
                searchVr.style.display = 'block';
                filterICBox.style.display = "<?php echo accountProperties('Filter IC') ? 'block' : 'none';?>";
                filterICVr.style.display = "<?php echo accountProperties('Filter IC') ? 'block' : 'none';?>";
                archiveExportButton.style.display = 'inline-block';
                htmlStr = "No buildings found with this filter.";
            }
        } else {
            filterManagerBox.style.display = 'none';
            filterManagerVr.style.display = 'none';
            searchBox.style.display = 'none';
            searchVr.style.display = 'none';
            archiveExportButton.style.display = 'none';
            filterICBox.style.display = 'none';
            filterICVr.style.display = 'none';
            htmlStr = "No buildings archived for this date.";
        }
        archiveTable.innerHTML = htmlStr;
    }
</script>
<script src="/js/setFiltersArchive.js"></script>
<script src="/js/adjustMainMargin.js"></script>
<script src="/js/tableToExcel.js"></script>
<script>
    document.getElementById('searchArchivesForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const searchTerm = document.getElementById('searchArchives').value.toLowerCase();
        const tbody = document.getElementById('archiveTable').getElementsByTagName('tbody')[0];
        const trs = Array.from(tbody.children);

        // filter rows
        trs.forEach(tr => {
            tr.style.display = tr.children[0].textContent.toLowerCase().includes(searchTerm)
                ? 'table-row'
                : 'none';
        });

        // reapply alternating colors
        const visibleRows = trs.filter(tr => tr.style.display !== 'none');
        visibleRows.forEach((tr, i) => {
            tr.classList.remove('even', 'odd');
            tr.classList.add(i % 2 === 0 ? 'even' : 'odd');
        });
    });
</script>
<style>
    tbody tr.even td {
        background-color: #E5E5E5;
    }
    tbody tr.odd td {
        background-color: #F3F3F3;
    }
</style>
</html>