document.getElementById("singleDateButton").onClick(function() {
    document.getElementById("archiveFromDate").hidden = true;
    document.getElementById("archiveToDate").hidden = true;
    document.getElementById("archiveSingleDate").hidden = false;
});
document.getElementById("dateRangeButton").onClick(function() {
    document.getElementById("archiveFromDate").hidden = false;
    document.getElementById("archiveToDate").hidden = false;
    document.getElementById("archiveSingleDate").hidden = true;
});

document.getElementById('filterManager').addEventListener('input', function() {
    buildTableArchive(archiveData);
});

document.getElementById('filterIC').addEventListener('input', function() {
    buildTableArchive(archiveData);
});

document.getElementById('archiveFromDate').addEventListener('input', async function() {
    archiveData = await getArchiveData();
    document.getElementById('filterManager').value = defaultManagerFilter;
    document.getElementById('filterIC').value = '---';
    buildTableArchive(archiveData);
});

document.getElementById('archiveToDate').addEventListener('input', async function() {
    archiveData = await getArchiveData();
    document.getElementById('filterManager').value = defaultManagerFilter;
    document.getElementById('filterIC').value = '---';
    buildTableArchive(archiveData);
});

async function getArchiveData() {
    const response = await fetch(
        '/php/getDataArchive.php?key=' + accessKey +
        '&archiveFromDate=' + document.getElementById('archiveFromDate').value +
        '&archiveToDate=' + document.getElementById('archiveToDate').value
    );
    const data = await response.json();
    return data;
}