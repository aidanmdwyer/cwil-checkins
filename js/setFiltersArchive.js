document.getElementById("singleDateButton").onclick(function() {
    document.getElementById("archiveFromDate").style.display = "default";
    document.getElementById("archiveToDate").style.display = "default";
    document.getElementById("archiveSingleDate").style.display = "none";
});
document.getElementById("dateRangeButton").onclick(function() {
    document.getElementById("archiveFromDate").style.display = "none";
    document.getElementById("archiveToDate").style.display = "none";
    document.getElementById("archiveSingleDate").style.display = "default";
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