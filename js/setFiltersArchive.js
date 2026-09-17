document.getElementById("singleDateButton").addEventListener('input', function() {
    document.getElementById("archiveFromDateLabel").style.display = "none";
    document.getElementById("archiveToDateLabel").style.display = "none";
    document.getElementById("archiveSingleDateLabel").style.display = "flex";
    document.getElementById("archiveFromDate").value = "";
    document.getElementById("archiveToDate").value = "";
    document.getElementById('filterManager').value = defaultManagerFilter;
    document.getElementById('filterIC').value = '---';
    resetArchiveTable();
    hideArchiveFilters();
});
document.getElementById("dateRangeButton").addEventListener('input', function() {
    document.getElementById("archiveFromDateLabel").style.display = "flex";
    document.getElementById("archiveToDateLabel").style.display = "flex";
    document.getElementById("archiveSingleDateLabel").style.display = "none";
    document.getElementById("archiveSingleDate").value = "";
    document.getElementById('filterManager').value = defaultManagerFilter;
    document.getElementById('filterIC').value = '---';
    resetArchiveTable();
    hideArchiveFilters();
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
        '&archiveSingleDate=' + document.getElementById('archiveSingleDate').value +
        '&archiveFromDate=' + document.getElementById('archiveFromDate').value +
        '&archiveToDate=' + document.getElementById('archiveToDate').value
    );
    const data = await response.json();
    return data;
}