const previewImport = document.getElementById('previewImport');
const commitImport = document.getElementById('commitImport');
let inputData = {};
const numRowsDiv = document.getElementById('numRowsDiv');
const numSuccessesDiv = document.getElementById('numSuccessesDiv');
const numUpdatedDiv = document.getElementById('numUpdatedDiv');
const numExistDiv = document.getElementById('numExistDiv');
const missingManagersDiv = document.getElementById('missingManagers');
const missingIcsDiv = document.getElementById('missingIcs');
const dayErrorsDiv = document.getElementById('dayErrors');
const errorCountDiv = document.getElementById('errorCount');
const importTable = document.getElementById('importTable');
const summaryTitle = document.getElementById('summaryTitle');

document.getElementById('csvFileInput').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let csvText = e.target.result;
            csvText = csvText.replaceAll('\n', ' ');
            let csvLines = csvText.split('\r');
            let foundHeader = false;
            let header = [];
            let importData = [];
            csvLines.forEach((line, index) => {
                if(foundHeader) {
                    let inQuotes = false;
                    let startIndex = 0;
                    let lineItems = parseCSVLine(line);

                    let buildingObj = {}
                    header.forEach((key, lineItemIndex) => {
                        buildingObj[key] = lineItems[lineItemIndex];
                    });
                    if(buildingObj['Account Name'] !== '' && buildingObj['Name'][0] !== ("*")) {
                        importData.push(buildingObj);
                    } else {
                        console.log('ignored: ' + buildingObj['Account Name'] + ', ' + buildingObj['Name'])
                    }

                    function parseCSVLine(line) {
                        line = line.trim().replaceAll('\n', '');
                        const fields = [];
                        let field = '';
                        let inQuotes = false;

                        for (let i = 0; i < line.length; i++) {
                            const ch = line[i];

                            if (ch === '"') {
                                if (inQuotes && line[i + 1] === '"') {
                                    field += '"';
                                    i++;
                                } else {
                                    inQuotes = !inQuotes;
                                }
                            } else if (ch === ',' && !inQuotes) {
                                fields.push(field);
                                field = '';
                            } else {
                                field += ch;
                            }
                        }

                        fields.push(field);
                        return fields;
                    }
                } else if(line.slice(-12) !== ',,,,,,,,,,,,') {
                    line = line.trim().replaceAll('\n', '');
                    header = line.split(',');
                    foundHeader = true;
                }
            });

            previewImport.style.display = 'inline-block';
            previewImport.disabled = false;
            commitImport.style.display = 'none';
            commitImport.disabled = false;
            summaryTitle.style.display = 'none';
            numRowsDiv.style.display = 'none';
            numSuccessesDiv.style.display = 'none';
            numUpdatedDiv.style.display = 'none';
            numExistDiv.style.display = 'none';
            missingManagersDiv.style.display = 'none';
            missingIcsDiv.style.display = 'none';
            dayErrorsDiv.style.display = 'none';
            errorCountDiv.style.display = 'none';
            importTable.innerHTML = '';

            inputData = convertImportData(importData);
        };
        reader.readAsText(file);
    }
});

function showImport(mode) {
    if(mode === 'preview') {
        summaryTitle.style.display = 'block';
        summaryTitle.innerHTML = `<h2>Preview Summary:</h2>`;

        numRowsDiv.innerHTML = "";
        numSuccessesDiv.innerHTML = "";
        numUpdatedDiv.innerHTML = "";
        numExistDiv.innerHTML = "";
        missingManagersDiv.innerHTML = "";
        missingIcsDiv.innerHTML = "";
        dayErrorsDiv.innerHTML = "";
        errorCountDiv.innerHTML = "";
        numUpdatedDiv.display = "none"
        numExistDiv.display = "none"
        missingManagersDiv.display = "none"
        missingIcsDiv.display = "none"
        dayErrorsDiv.display = "none"
        errorCountDiv.display = "none"

        commitImport.style.display = 'inline-block';
        previewImport.disabled = true;
    } else if(mode === 'commit') {
        summaryTitle.style.display = 'block';
        summaryTitle.innerHTML = `<h2>Commit Summary:</h2>`;

        commitImport.disabled = true;
    }
    inputData['mode'] = mode;
    buildImportTable(inputData);
}

function convertImportData(importData) {
    let retList = [];
    importData.forEach(building => {
        let obj = {};
        obj['name'] = building['Account Name'];
        obj['manager'] = (building['Night Manager'] !== '') ? building['Night Manager'] : building['FSM'];
        obj['ic'] = building['Name'];
        obj['monday'] = (building['Monday'] === 'Yes');
        obj['tuesday'] = (building['Tuesday'] === 'Yes');
        obj['wednesday'] = (building['Wednesday'] === 'Yes');
        obj['thursday'] = (building['Thursday'] === 'Yes');
        obj['friday'] = (building['Friday'] === 'Yes');
        obj['saturday'] = (building['Saturday'] === 'Yes');
        obj['sunday'] = (building['Sunday'] === 'Yes');
        retList.push(obj);
    });
    return {'records': retList};
}

function buildImportTable(importData) {
    const rowStatusCells = [];

    importTable.innerHTML = "";

    buildImportTable();
    sendToServer();

    function buildImportTable() {
        const headerRow = document.createElement('tr');
        headerRow.innerHTML =
            `<th>Name</th><th>Manager</th><th>IC</th>
             <th>M</th><th>T</th><th>W</th><th>Th</th><th>F</th><th>Sat</th><th>Sun</th>
             <th>Status</th>`;
        importTable.appendChild(headerRow);

        importData['records'].forEach(building => {
            const tr = document.createElement('tr');

            /* fixed‑text columns */
            ['name', 'manager', 'ic'].forEach(k => {
                const td = document.createElement('td');
                td.textContent = building[k] ?? '';
                tr.appendChild(td);
            });

            /* day flags */
            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
                .forEach(d => {
                    const td = document.createElement('td');
                    if (building[d]) td.style.backgroundColor = 'lightgreen';
                    tr.appendChild(td);
                });

            /* status placeholder */
            const statusTd = document.createElement('td');
            statusTd.className = 'status';
            tr.appendChild(statusTd);
            rowStatusCells.push(statusTd);

            importTable.appendChild(tr);
        });
    }

    async function sendToServer() {
        try {
            const resp = await fetch('/php/importInsert.php?key=' + encodeURIComponent(accessKey), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(importData)
            });
            const results = await resp.json();

            if(results['numRows'] > 0) {
                numRowsDiv.style.display = 'block';
                numRowsDiv.innerHTML = `<strong>` + results['numRows'] + ` Rows</strong><br><br>`;
                numSuccessesDiv.style.display = 'block';
                numSuccessesDiv.innerHTML = `<strong style="color: green">` + results['numSuccesses'] + ` Successful Insertions</strong><br><br>`;
                if(results['numExist'] > 0) {
                    numExistDiv.style.display = 'block';
                    numExistDiv.innerHTML = `<strong style="color: darkOrange">` + results['numExist'] + ` Already Exist</strong><br><br>`;
                }
                if(results['numUpdated'] > 0) {
                    numUpdatedDiv.style.display = 'block';
                    numUpdatedDiv.innerHTML = `<strong style="color: darkOrange">` + results['numUpdated'] + ` Updated</strong><br><br>`;
                }
                if(results['missingManagers'].length > 0) {
                    missingManagersDiv.style.display = 'block';
                    missingManagersDiv.innerHTML = `
<button class="big" id="importMissingManagers">Import Missing Managers</button><br>
<strong style="color: red">` + results['missingManagers'].length + ` Missing Managers:</strong><br>` + results['missingManagers'].join(`<br>`) + `<br><br>`;
                    const importMissingManagers = document.getElementById('importMissingManagers')
                    importMissingManagers.onclick = () => {
                        importMissing(results['missingManagers'], 'managers');
                        importMissingManagers.disabled = true;
                        document.getElementById('previewImport').disabled = false;
                    }
                }
                if(results['missingIcs'].length > 0) {
                    missingIcsDiv.style.display = 'block';
                    missingIcsDiv.innerHTML = `
<button class="big" id="importMissingContractors">Import Missing Contractors</button><br>
<strong style="color: red">` + results['missingIcs'].length + ` Missing Contractors:</strong><br>` + results['missingIcs'].join(`<br>`) + `<br><br>`;
                    const importMissingContractors = document.getElementById('importMissingContractors')
                    importMissingContractors.onclick = () => {
                        importMissing(results['missingIcs'], 'contractors')
                        importMissingContractors.disabled = true;
                        document.getElementById('previewImport').disabled = false;
                    }
                }
                if(results['dayErrors'].length > 0) {
                    dayErrorsDiv.style.display = 'block';
                    dayErrorsDiv.innerHTML = `<strong style="color: red">` + results['dayErrors'].length + ` Day Errors:</strong><br>` + results['dayErrors'].join(`<br>`) + `<br><br>`;
                }
                if(results['errorCount'] > 0) {
                    errorCountDiv.style.display = 'block';
                    errorCountDiv.innerHTML = `<strong style="color: red">` + results['errorCount'] + ` Failed Insertions:</strong><br>` + results['managerErrorCount'] + ` Manager Errors<br>` + results['icErrorCount'] + ` Contractor Errors<br>` + results['dayErrors'].length + ` Day Errors<br>` + results['otherErrorCount'] + ` Other Errors<br><br>`;
                }
            }

            results['results'].forEach((r, i) => {
                if (r.success) {
                    rowStatusCells[i].textContent = r.message || 'Inserted';
                    rowStatusCells[i].style.color = (r.message === 'Updated' || r.message === 'Would Update' || r.message === 'Already Exists') ? 'orange' : 'green';
                } else {
                    rowStatusCells[i].textContent = `Error: ${r.error}`;
                    rowStatusCells[i].style.color = 'red';
                }
            });
        } catch (e) {
            console.error(e);
            rowStatusCells.forEach(td => {
                td.textContent = 'Fetch error';
                td.style.color = 'red';
            });
        }
    }
}

async function importMissing(missing, type) {
    if (!Array.isArray(missing) || missing.length === 0) {
        alert("No items to import");
        return;
    }

    try {
        let response = await fetch("/php/importMissing.php?key=" + encodeURIComponent(accessKey), {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                type: type,
                items: missing
            })
        });

        let result = await response.json();

        if (result.success) {
            alert(`${result.inserted} ${type} imported successfully!`);
        } else {
            alert("Error: " + result.error);
        }
    } catch (err) {
        console.error(err);
        alert("Failed to import — check console for details.");
    }
}