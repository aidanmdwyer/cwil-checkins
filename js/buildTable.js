function buildTable(fetchStr = './php/getData.php?key=' + accessKey +
                        '&manager=' + encodeURIComponent(document.getElementById('filterManager').value) +
                        '&ic=' + encodeURIComponent(document.getElementById('filterIC').value) +
                        '&todayOnly=' + (document.getElementById('todayOnly').checked ? 'true' : 'false') +
                        '&showActive=' + (document.getElementById('showActive').checked ? 'true' : 'false') +
                        '&search=' + (document.getElementById('searchBuildings').value),
                    respectLoadAll = false,
                    cancelSidebar = true,
                    selected = [],
                    showLoadingText = true) {

    let accountProperties;
    if(respectLoadAll) fetchStr += '&loadAll=' + (document.getElementById('loadAll').style.display === 'none' ? 'true' : 'false');

    fetch('/php/accountProperties.php', {
        headers: {'Accept': 'application/json'}
    })
        .then(response => response.json())
        .then(data => {
            accountProperties = data;

            if(cancelSidebar) cancel();
            if(showLoadingText) {
                refreshButton.disabled = true;
                refreshButton.innerText = 'Loading';
            }
            fetch(fetchStr).then(response => response.json()).then(data => {

                const tableColumns = {
                    'Select/Edit Multiple Buildings' :
                        [
                            `<th><input id="selectAll" type="checkBox" onclick="handleSelectAll(this.checked)"></th>`,
                            (rowData, bgColor) =>
                                `<td style="background-color: ${bgColor}; text-align: center;"><input class="selectBox" type="checkbox" name="checkedNames[]" value="${rowData['name']}" onclick="handleSelect()"></td>`
                        ],
                    'See Building Name' :
                        [
                            `<th>Building Name</th>`,
                            (rowData, bgColor) =>
                                `<td style="background-color: ${bgColor}">${encodeHTML(rowData['name'])}</td>`
                        ],
                    'See Manager' :
                        [
                            `<th>Manager</th>`,
                            (rowData, bgColor) =>
                                `<td style="background-color: ${bgColor}">${encodeHTML(rowData['manager'])}</td>`
                        ],
                    'See IC' :
                        [
                            `<th>IC</th>`,
                            (rowData, bgColor) =>
                                `<td style="background-color: ${bgColor}">${encodeHTML(rowData['ic'])}</td>`
                        ],
                    'See Check-in Status':
                        [
                            `<th>&#9989</th>`,
                            (rowData, bgColor) =>
                                `<td style="background-color: ${bgColor}">
                                  <button
                                    type="button"
                                    style="text-align:center; background-color: transparent; border: none;"
                                    data-id="${rowData.id}"
                                    data-name="${encodeHTML(rowData.name)}"
                                    data-checked="${rowData.checked}"
                                    ${(accountProperties.includes('Can Toggle Check-ins') || accountProperties === '*') ? `
                                    onclick="toggleCheck(
                                      this.dataset.name,
                                      this.dataset.checked
                                    )"
                                    ` : ``}
                                  >
                                    ${(rowData.checked === 0) ? '&#10060' : '&#9989'}
                                  </button>
                                </td>`
                        ],
                    'See Check-in Time':
                        [
                            `<th>Time Checked</th>`,
                            (rowData, bgColor) => {
                                const rawTime = rowData['checkedTime'];
                                let formatted = '';

                                if (rawTime) {
                                    const date = new Date(rawTime);
                                    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                                        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                                    const month = monthNames[date.getMonth()];
                                    const day = date.getDate();

                                    let hours = date.getHours();
                                    const minutes = date.getMinutes();
                                    const ampm = hours >= 12 ? 'PM' : 'AM';

                                    hours = hours % 12;
                                    hours = hours ? hours : 12;
                                    const minutesStr = minutes < 10 ? '0' + minutes : minutes;

                                    formatted = `${month} ${day}, ${hours}:${minutesStr} ${ampm}`;
                                }

                                return `<td style="background-color: ${bgColor}; text-align: center; font-family: 'Arial', sans-serif;">${formatted}</td>`;
                            }
                        ],
                    'See Days' :
                        [
                            `<th style="font-size: 10px; padding: 0;">M</th>
                    <th style="font-size: 10px; padding: 0; min">T</th>
                    <th style="font-size: 10px; padding: 0;">W</th>
                    <th style="font-size: 10px; padding: 0;">Th</th>
                    <th style="font-size: 10px; padding: 0;">F</th>
                    <th style="font-size: 10px; padding: 0;">Sat</th>
                    <th style="font-size: 10px; padding: 0;">Sun</th>`,
                            (rowData, bgColor) =>
                                `
                        <td style="text-align: center; width: 25px; background-color: ${bgColor}; padding: 0;">${(rowData['monday'] === 1) ? '<span style="font-size: 10px;">M</span>' : ''}</td>
                        <td style="text-align: center; width: 25px; background-color: ${bgColor}; padding: 0;">${(rowData['tuesday'] === 1) ? '<span style="font-size: 10px;">T</span>' : ''}</td>
                        <td style="text-align: center; width: 25px; background-color: ${bgColor}; padding: 0;">${(rowData['wednesday'] === 1) ? '<span style="font-size: 10px;">W</span>' : ''}</td>
                        <td style="text-align: center; width: 25px; background-color: ${bgColor}; padding: 0;">${(rowData['thursday'] === 1) ? '<span style="font-size: 10px;">Th</span>' : ''}</td>
                        <td style="text-align: center; width: 25px; background-color: ${bgColor}; padding: 0;">${(rowData['friday'] === 1) ? '<span style="font-size: 10px;">F</span>' : ''}</td>
                        <td style="text-align: center; width: 25px; background-color: ${bgColor}; padding: 0;">${(rowData['saturday'] === 1) ? '<span style="font-size: 10px;">Sat</span>' : ''}</td>
                        <td style="text-align: center; width: 25px; background-color: ${bgColor}; padding: 0;">${(rowData['sunday'] === 1) ? '<span style="font-size: 10px;">Sun</span>' : ''}</td>
                        `
                        ],
                    'Print QR' :
                        [
                            `<th>QR</th>`,
                            (rowData, bgColor) =>
                                `<td style="background-color: ${bgColor}"><button type="button" style="text-align:center; background-color: transparent; border: none;" onclick="openQr(JSON.parse(decodeURIComponent(this.dataset.row)))" data-row="${encodeURIComponent(JSON.stringify(rowData))}"><span style="font-size:15px;">&#9635</span></button></td>`
                        ],
                    'Edit Buildings' :
                        [
                            `<th>Edit</th>`,
                            (rowData, bgColor) =>
                                `<td style="background-color: ${bgColor}"><button type="button" style="text-align:center; background-color: transparent; border: none;" onclick="editRow(JSON.parse(decodeURIComponent(this.dataset.row)))" data-row="${encodeURIComponent(JSON.stringify(rowData))}">&#128394</button></td>`
                        ]
                }

                let colorSwitch = false;
                buildingsTable.style.display = 'inline-block';

                document.getElementById('inactiveText').style.display = (document.getElementById('showActive').checked) ? 'none' : 'block';

                if(data['rows'].length > 0) {
                    let tableHeader = '';
                    Object.keys(tableColumns).forEach(label => {
                        if(accountProperties.includes(label) || accountProperties === '*') {
                            tableHeader += tableColumns[label][0];
                        }
                    });

                    buildingsTable.innerHTML = `<thead><tr>` + tableHeader + `</tr></thead>`;

                    if(document.getElementById('todayOnly').checked) {
                        let days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                        let numOver = -1;
                        Array.from(buildingsTable.rows[0].cells).forEach((cell, index) => {
                            if(cell.textContent === 'M') {
                                numOver = index;
                            }
                        });
                        if(numOver !== -1) {
                            let dayIndex = numOver + days.indexOf(data['dayOfWeek']);
                            let cell = buildingsTable.rows[0].cells[dayIndex];

                            cell.innerHTML = `
                              <div style="
                                height: 22px;
                                width: 22px;
                                border-radius: 50%;
                                background-color: yellow;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 10px;
                                margin: auto;
                              ">
                                ${cell.innerHTML}
                              </div>
                            `;
                        }
                    }

                    //buildingsTable.innerHTML += `<tbody>`;
                    let allSelected = true;
                    data['rows'].forEach(rowData => {
                        let bgColor = colorSwitch ? '#F3F3F3' : '#E5E5E5';

                        let tr = document.createElement('tr');
                        tr.setAttribute('data-name', rowData['name']);

                        Object.keys(tableColumns).forEach(label => {
                            if(accountProperties.includes(label) || accountProperties === '*') {
                                tr.innerHTML += tableColumns[label][1](rowData, bgColor);
                            }
                        });

                        buildingsTable.appendChild(tr);

                        const checkbox = tr.querySelector('.selectBox');
                        if (checkbox && selected.includes(checkbox.value)) {
                            checkbox.checked = true;
                        } else {
                            allSelected = false;
                        }

                        colorSwitch = !colorSwitch;
                    });
                    if(allSelected) {
                        document.getElementById('selectAll').checked = true;
                    }

                    //buildingsTable.innerHTML += `</tbody>`;

                    loadAll.style.display = 'none';
                    if(!fetchStr.includes('loadAll=true') && data['hasMore'] === true) {
                        loadAll.style.display = 'block';
                        loadAll.onclick = () => {
                            buildTable(fetchStr + '&loadAll=true')
                        }
                    }

                    refreshButton.disabled = false;
                    refreshButton.innerHTML = 'Refresh';
                } else {
                    loadAll.style.display = 'none';
                    refreshButton.disabled = false;
                    refreshButton.innerHTML = 'Refresh';
                    buildingsTable.innerHTML = 'No buildings matching filter.';
                    selectSubmits.style.display = 'none';
                }
            }).catch(error => {
                buildingsTable.innerHTML = error;
                buildingsTable.style.display = 'inline-block';
                refreshButton.disabled = false;
                refreshButton.innerHTML = 'Refresh';
                loadAll.style.display = 'none';
            });

        }).catch(error => console.error('Error fetching account properties:', error));
}