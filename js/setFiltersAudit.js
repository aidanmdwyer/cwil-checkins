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
const pageSelector = document.getElementById("pageSelector");
const currentPageElement = document.getElementById("currentPage");
const numPagesElement = document.getElementById("numPages");
const backButton = document.getElementById("backButton");
const skipBackButton = document.getElementById("skipBackButton");
const forwardButton = document.getElementById("forwardButton");
const skipForwardButton = document.getElementById("skipForwardButton");

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

async function buildAuditList(page = 1) {
    document.getElementsByClassName("left")[0].scrollTop = 0;

    pageSelector.style.display = "none";

    const response = await fetch(
        '/php/getDataAudit.php?key=' + accessKey +
        '&filterFromDate=' + filterFromDate.value +
        '&filterToDate=' + filterToDate.value +
        '&filterBuildingName=' + filterBuildingName.value +
        '&filterAuditor=' + filterAuditor.value +
        '&filterIC=' + filterIC.value +
        '&page=' + page
    );
    
    const data = await response.json();

    console.log(data);

    const rows = data['rows'];
    const currentPage = Number(data['page']);
    const numPages = Number(data['numPages']);

    auditList.innerHTML = "";
    rows.forEach(audit => {
        const div = document.createElement("div");
        div.onclick = function () {openAudit(audit)};
        const auditDate = new Date(audit['date']).toLocaleDateString('en-CA');
        div.innerHTML = `
        <div><h3>` + audit['buildingName'] + `</h3><p>` + audit['auditor'] + `</p></div>` +
        `<div><p>` + auditDate + "</p><p>" + audit['ic'] + `</p></div>`;

        auditList.appendChild(div);
    });

    if(numPages > 1) {
        pageSelector.style.display = "flex";
        currentPageElement.innerText = currentPage;
        numPagesElement.innerText = numPages;

        if(numPages > 2) {
            skipBackButton.disabled = false;
            skipForwardButton.disabled = false;
            skipBackButton.onclick = (() => {
                buildAuditList();
            });
            skipForwardButton.onclick = (() => {
                buildAuditList(numPages);
            });
        } else {
            skipBackButton.disabled = true;
            skipForwardButton.disabled = true;
        }

        if(currentPage === 1) {
            backButton.disabled = true;
            skipBackButton.disabled = true;
        } else {
            backButton.disabled = false;
            backButton.onclick = (() => {
                buildAuditList(currentPage - 1);
            });
        }

        if(currentPage === numPages) {
            forwardButton.disabled = true;
            skipForwardButton.disabled = true;
        } else {
            forwardButton.disabled = false;
            forwardButton.onclick = (() => {
                buildAuditList(currentPage + 1);
            });
        }
    }
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
        if('itemValue' in item) {
            valueElement.innerHTML = item['itemValue'] == 1 ? '✅' : (item['itemValue'] == 0 ? '❌' : 'N/A');
        } else {
            valueElement.innerHTML = "Error";
        }

        const commentElement = document.getElementById(desc + "-comment");
        if(item['comment']) {
            commentElement.innerHTML = item['comment'];
        } else {
            commentElement.innerHTML = "-";
        }
    });
}