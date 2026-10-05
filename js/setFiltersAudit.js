const filterBuildingName = document.getElementById("filterBuildingName");
const filterAuditor = document.getElementById("filterAuditor");
const filterIC = document.getElementById("filterIC");
const auditList = document.getElementById("auditList");
const displayBuildingName = document.getElementById("displayBuildingName");
const displayAuditor = document.getElementById("displayAuditor");
const displayIC = document.getElementById("displayIC");
const displayDate = document.getElementById("displayDate");
const auditTable = document.getElementById("auditTable");
const displayAudit = document.getElementById("displayAudit");
const noAuditSelectedText = document.getElementById("noAuditSelectedText");

filterBuildingName.addEventListener('input', function () {
    buildAuditList();
});
filterAuditor.addEventListener('input', function () {
    buildAuditList();
});
filterIC.addEventListener('input', function () {
    buildAuditList();
});

async function loadAuditFilterData() {
    const response = await fetch(
        '/php/getAuditFilterOptions.php?key=' + accessKey
    );
    
    const data = await response.json();

    data['buildingNames'].forEach(buildingName => {
        const option = document.createElement("option");
        option.innerHTML = buildingName;
        filterBuildingName.appendChild(option);
    });
    data['auditors'].forEach(auditor => {
        const option = document.createElement("option");
        option.innerHTML = auditor;
        filterAuditor.appendChild(option);
    });
    data['ics'].forEach(ic => {
        const option = document.createElement("option");
        option.innerHTML = ic;
        filterIC.appendChild(option);
    });
}

async function buildAuditList() {
    const response = await fetch(
        '/php/getDataAudit.php?key=' + accessKey +
        '&filterBuildingName=' + document.getElementById('filterBuildingName').value +
        '&filterAuditor=' + document.getElementById('filterAuditor').value +
        '&filterIC=' + document.getElementById('filterIC').value
    );
    
    const data = await response.json();

    auditList.innerHTML = "";
    if(Object.keys(data).length > 0) {
        data.forEach(audit => {
            const div = document.createElement("div");
            div.onclick = function () {openAudit(audit)};
            audit['date'] = new Date(audit['date']).toLocaleDateString('en-CA');
            div.innerHTML = `
            <div><h3>` + audit['buildingName'] + `</h3><p>` + audit['auditor'] + `</p></div>` +
            `<div><p>` + audit['date'] + "</p><p>" + audit['ic'] + `</p></div>`;

            auditList.appendChild(div);
        });
    } else {
        const div = document.createElement(div);
        div.innerHTML = "No audits were found.";
        auditList.appendChild(div);
    }
}

async function openAudit({auditId, buildingName, auditor, date, ic}) {
    displayBuildingName.innerHTML = buildingName;
    displayAuditor.innerHTML = auditor;
    displayDate.innerHTML = date;
    displayIC.innerHTML = ic;

    displayAudit.style.display = "flex";
    noAuditSelectedText.style.display = "none";

    const response = await fetch(
        '/php/getDataIndividualAudit.php?key=' + accessKey +
        '&auditId=' + auditId
    );
    
    const data = await response.json();

    data.forEach(item => {
        const desc = item['itemDescription'];
        const valueElement = document.getElementById(desc + "-value");
        valueElement.innerHTML = item['itemValue'] == 1 ? '✅' : '❌'
        const commentElement = document.getElementById(desc + "-comment");
        if(item['comment']) {
            commentElement.innerHTML = item['comment'];
        } else {
            commentElement.innerHTML = "-";
        }
    });
}