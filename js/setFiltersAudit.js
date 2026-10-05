document.getElementById('filterBuildingName').addEventListener('input', function () {
    buildTableAudit();
});
document.getElementById('filterAuditor').addEventListener('input', function () {
    buildTableAudit();
});
document.getElementById('filterIC').addEventListener('input', function () {
    buildTableAudit();
});

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