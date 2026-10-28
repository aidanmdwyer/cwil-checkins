const filterBuildingName = document.getElementById("filterBuildingName");
const filterAuditor = document.getElementById("filterAuditor");
const filterIC = document.getElementById("filterIC");
const auditList = document.getElementById("auditList");
const displayBuildingName = document.getElementById("displayBuildingName");
const displayAuditor = document.getElementById("displayAuditor");
const displayIC = document.getElementById("displayIC");
const displayDate = document.getElementById("displayDate");
const displayTime = document.getElementById("displayTime");
const auditTable = document.getElementById("auditTable");
const displayAudit = document.getElementById("displayAudit");
const noAuditSelectedText = document.getElementById("noAuditSelectedText");
const deleteAuditButton = document.getElementById("deleteAuditButton");
const filterFromDate = document.getElementById("filterFromDate");
const filterToDate = document.getElementById("filterToDate");

filterFromDate.addEventListener('input', async function() {
    filterToDate.disabled = false;
    filterToDate.min = filterFromDate.value;
    filterToDate.value = filterFromDate.value;
    filterToDate.dispatchEvent(new Event('input'));
});

filterToDate.addEventListener('input', async function() {
    if(filterFromDate.value) {
        buildAuditList();
    }
});

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
        '&filterFromDate=' + filterFromDate.value +
        '&filterToDate=' + filterToDate.value +
        '&filterBuildingName=' + filterBuildingName.value +
        '&filterAuditor=' + filterAuditor.value +
        '&filterIC=' + filterIC.value +
        '&page=' + 1
    );
    
    const data = await response.json();

    console.log(data);

    auditList.innerHTML = "";
    data.forEach(audit => {
        const div = document.createElement("div");
        div.onclick = function () {openAudit(audit)};
        const auditDate = new Date(audit['date']).toLocaleDateString('en-CA');
        div.innerHTML = `
        <div><h3>` + audit['buildingName'] + `</h3><p>` + audit['auditor'] + `</p></div>` +
        `<div><p>` + auditDate + "</p><p>" + audit['ic'] + `</p></div>`;

        auditList.appendChild(div);
    });
}

async function openAudit({auditId, buildingName, auditor, date, ic}) {
    displayBuildingName.innerHTML = buildingName;
    displayAuditor.innerHTML = auditor;
    displayDate.innerHTML = new Date(date).toLocaleDateString('en-CA');
    displayTime.innerHTML = new Date(date).toLocaleTimeString('en-US', {hour: 'numeric', minute:'numeric'});
    displayIC.innerHTML = ic;

    displayAudit.style.display = "flex";
    noAuditSelectedText.style.display = "none";

    deleteAuditButton.onclick = (() => {
        if(confirm('Are you sure you want to delete this audit of ' + buildingName + '?')) {
            window.location.href='/php/deleteAudit.php?auditId=' + auditId + '&buildingName=' + buildingName;
        }
    });

    const response = await fetch(
        '/php/getDataIndividualAudit.php?key=' + accessKey +
        '&auditId=' + auditId
    );
    
    const data = await response.json();

    console.log(data);

    data.forEach(item => {
        const desc = item['itemDescription'];

        const valueElement = document.getElementById(desc + "-value");
        valueElement.innerHTML = "";
        valueElement.innerHTML = item['itemValue'] == 1 ? '✅' : '❌';

        const commentElement = document.getElementById(desc + "-comment");
        if(item['comment']) {
            commentElement.innerHTML = item['comment'];
        } else {
            commentElement.innerHTML = "-";
        }
    });
}