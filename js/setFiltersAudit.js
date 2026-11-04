const filterBuildingName = document.getElementById("filterBuildingName");
const filterAuditor = document.getElementById("filterAuditor");
const filterIC = document.getElementById("filterIC");
const auditList = document.getElementById("auditList");
const displayBuildingName = document.getElementById("displayBuildingName");
const displayAuditor = document.getElementById("displayAuditor");
const displayIC = document.getElementById("displayIC");
const displayManager = document.getElementById("displayManager");
const displayDate = document.getElementById("displayDate");
const displayTime = document.getElementById("displayTime");
const displayValues = document.getElementById("displayValues");
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
const displayClosetImage = document.getElementById("displayClosetImage");

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

async function loadAuditFilterData(defaultBuildingName = "---") {
    filterBuildingName.innerHTML = "<option>---</option>";
    filterAuditor.innerHTML = "<option>---</option>";
    filterIC.innerHTML = "<option>---</option>";

    const response = await fetch(
        '/php/getAuditFilterOptions.php?key=' + accessKey
    );
    
    const data = await response.json();

    data['buildingNames'].forEach(buildingName => {
        const option = document.createElement("option");
        option.value = buildingName;
        option.textContent = buildingName;
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

    filterBuildingName.value = defaultBuildingName;

    return true;
}

async function buildAuditList(page = 1) {
    document.getElementsByClassName("left")[0].scrollTop = 0;

    pageSelector.style.display = "none";

        const params = new URLSearchParams({
        key: accessKey,
        filterFromDate: filterFromDate.value,
        filterToDate: filterToDate.value,
        filterBuildingName: filterBuildingName.value,
        filterAuditor: filterAuditor.value,
        filterIC: filterIC.value,
        page: page
    });

    const response = await fetch('/php/getDataAudit.php?' + params);

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

async function openAudit({auditId, buildingName, auditor, date, ic, manager}) {
    displayBuildingName.innerHTML = buildingName ?? "";
    displayAuditor.innerHTML = auditor ?? "";
    displayDate.innerHTML = new Date(date).toLocaleDateString('en-CA') ?? "";
    displayTime.innerHTML = new Date(date).toLocaleTimeString('en-US', {hour: 'numeric', minute:'numeric'}) ?? "";
    displayIC.innerHTML = ic ?? "";
    displayManager.innerHTML = manager ?? "";
    displayClosetImage.style.display = "none";
    displayClosetImage.src = "";

    displayAudit.style.display = "flex";
    noAuditSelectedText.style.display = "none";

    deleteAuditButton.onclick = (() => {
        if(confirm('Are you sure you want to delete this audit of ' + buildingName + '?')) {
            window.location.href='/php/deleteAudit.php?auditId=' + auditId + '&buildingName=' + buildingName;
        }
    });

    Array.from(document.getElementsByClassName('valueBox')).forEach((valueBox) => {
        valueBox.firstElementChild.innerHTML = "";
    });
    Array.from(document.getElementsByClassName('imageBox')).forEach((valueBox) => {
        valueBox.firstElementChild.style.display = "none";
    });

    const response = await fetch(
        '/php/getDataIndividualAudit.php?key=' + accessKey +
        '&auditId=' + auditId
    );
    
    const data = await response.json();

    console.log(data);

    if(data['closetImage']) {
        displayClosetImage.style.display = "inline-block";
        displayClosetImage.src = 'data:image/jpeg;base64,' + data['closetImage'];
    }

    let valueCounts = {};
    data['items'].forEach(item => {
        const value = item['itemValue'];
        const valueCount = valueCounts[value];
        if(valueCount) {
            valueCounts[value] = valueCount + 1;
        } else {
            valueCounts[value] = 1;
        }

        const desc = item['itemDescription'];

        const valueElement = document.getElementById(desc + "-value");
        valueElement.innerHTML = "";
        valueElement.innerHTML = item['itemValue'] == 1 ? '✅' : (item['itemValue'] == 0 ? '❌' : 'N/A');

        const commentElement = document.getElementById(desc + "-comment");
        if(item['comment']) {
            commentElement.innerHTML = item['comment'];
        } else {
            commentElement.innerHTML = "-";
        }

        const imagePreviewButton = document.getElementById(desc + "-imagePreviewButton");
        if(item['image']) {
            imagePreviewButton.style.display = "block";
            imagePreviewButton.dataset.imgSrc = 'data:image/jpeg;base64,' + item['image'];
        } else {
            imagePreviewButton.style.display = "none";
            imagePreviewButton.dataset.imgSrc = "";
        }
    });

    console.log(valueCounts);

    displayValues.innerHTML = (valueCounts[1] ?? "0") + " ✅<br>" + (valueCounts[0] ?? "0") + " ❌<br>" + (valueCounts[2] ?? "0") + " N/A";
}