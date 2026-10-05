const filterBuildingName = document.getElementById("filterBuildingName");
const filterAuditor = document.getElementById("filterAuditor");
const filterIC = document.getElementById("filterIC");

filterBuildingName.addEventListener('input', function () {
    buildTableAudit();
});
filterAuditor.addEventListener('input', function () {
    buildTableAudit();
});
filterIC.addEventListener('input', function () {
    buildTableAudit();
});

async function loadAuditFilterData() {
    let response;
    response = await fetch(
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

async function buildTableAudit() {
    let response;
    response = await fetch(
        '/php/getDataAudit.php?key=' + accessKey +
        '&filterBuildingName=' + document.getElementById('filterBuildingName').value +
        '&filterAuditor=' + document.getElementById('filterAuditor').value +
        '&filterIC=' + document.getElementById('filterIC').value
    );
    
    const data = await response.json();

    console.log(data);
}