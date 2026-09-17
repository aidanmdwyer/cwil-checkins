<?php
include 'login.php';
include 'accountProperties.php';

if (!accountProperties('Archives Page')) {
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
    <h3>Check-In Archives <a href="../php/instructions.php#Archives" target="_blank"><img src="../imgs/helpIconWhite.png" alt="help" style="width: 15px; height: 15px;"></a></h3>
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
                <div style="display: flex; flex-direction: column; ">
                    <label style="display: flex; flex-direction: row;">
                        <input type="radio" id="singleDateButton" name="dateType" checked>
                        Single Date
                    </label>
                    <label style="display: flex; flex-direction: row;">
                        <input type="radio" id="dateRangeButton" name="dateType">
                        Date Range
                    </label>
                </div>
                <label id="archiveSingleDateLabel" style="display: flex; flex-direction: column;">
                    Archive Date
                    <input type="date" id="archiveSingleDate" name="archiveSingleDate">
                </label>
                <label id="archiveFromDateLabel" style="display: flex; flex-direction: column; display: none;">
                    From Date
                    <input type="date" id="archiveFromDate" name="archiveFromDate">
                </label>
                <label id="archiveToDateLabel" style="display: flex; flex-direction: column; display: none;">
                    To Date
                    <input type="date" id="archiveToDate" name="archiveToDate">
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
        <h2 id="archiveText" style="margin-bottom: 0;"></h2>
        <div id="checkInCounter"></div>
        <div id="tableContainer" style="display: inline-block;">
            <table id="archiveTable" style="display: inline-block;"></table>
        </div>
    </div>
</main>
</body>


<script src="/js/loadElementVariables.js"></script>
<script src="/js/encodeHTML.js"></script>
<script src="/js/fillSelectMenu.js"></script>
<script>
    let archiveTable = document.getElementById('archiveTable');
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

    function resetArchiveTable() {
        document.getElementById("archiveText").innerHTML = "No Date Selected";
        document.getElementById("checkInCounter").innerHTML = "";
        document.getElementById("archiveTable").innerHTML = "<br>Select a date above to see archive.";
    }
    resetArchiveTable();

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
        const archiveText = document.getElementById("archiveText");

        if(document.getElementById('singleDateButton')) { //single date
            const singleInput = document.getElementById('archiveSingleDate').value; //"2025-07-08"
            const [singleYear, singleMonth, singleDay] = singleInput.split('-');
            const singleDate = new Date(singleYear, singleMonth - 1, singleDay); //Note: month is 0-indexed

            archiveText.innerText = 
                singleDate.toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'});
        } else { //range
            const fromInput = document.getElementById('archiveFromDate').value; //"2025-07-08"
            const [fromYear, fromMonth, fromDay] = fromInput.split('-');
            const fromDate = new Date(fromYear, fromMonth - 1, fromDay); //Note: month is 0-indexed
            const toInput = document.getElementById('archiveToDate').value;
            const [toYear, toMonth, toDay] = toInput.split('-');
            const toDate = new Date(toYear, toMonth - 1, toDay);

            if(toDate > fromDate) { //valid
                document.getElementById('archiveText').innerText = 
                    fromDate.toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'}) + 
                    " to " + toDate.toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'});
            } else { //invalid
                archiveText.innerText = "To Date must be later than From Date.";
            }
        }
        

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
                let numChecked = 0;
                filteredData.forEach(rowData => {
                    if(rowData.checked) numChecked++;

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

                const numBuildings = filteredData.length;
                const percentageChecked = Math.round(numChecked/numBuildings*1000)/10
                let checkInCounterHTML = 
                    "<span style='margin-right: 30px;'>" + numBuildings + " buildings loaded</span>" + 
                    "<span style='margin-right: 30px;'>" + numChecked + "/" + numBuildings + " &#9989 (" + percentageChecked + "%)" + 
                    "</span>" + (numBuildings - numChecked) + "/" + numBuildings + " &#10060 (" + (100 - percentageChecked) + "%)";
                document.getElementById("checkInCounter").innerHTML = checkInCounterHTML;

                htmlStr += `</tbody>`;
            } else {
                filterManagerBox.style.display = 'block';
                filterManagerVr.style.display = 'block';
                searchBox.style.display = 'block';
                searchVr.style.display = 'block';
                filterICBox.style.display = "<?php echo accountProperties('Filter IC') ? 'block' : 'none';?>";
                filterICVr.style.display = "<?php echo accountProperties('Filter IC') ? 'block' : 'none';?>";
                archiveExportButton.style.display = 'inline-block';
                archiveTable.innerHTML = "";
                document.getElementById("checkInCounter").innerHTML = "";
                htmlStr = "<br>No buildings found with this filter.";
            }
        } else {
            hideArchiveFilters();
            archiveTable.innerHTML = "";
            document.getElementById("checkInCounter").innerHTML = "";
            htmlStr = "<br>No buildings archived for this " + ((toInput) ? "range." : "date.");
        }
        archiveTable.innerHTML = htmlStr;
    }

    function hideArchiveFilters() {
        filterManagerBox.style.display = 'none';
        filterManagerVr.style.display = 'none';
        searchBox.style.display = 'none';
        searchVr.style.display = 'none';
        archiveExportButton.style.display = 'none';
        filterICBox.style.display = 'none';
        filterICVr.style.display = 'none';
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