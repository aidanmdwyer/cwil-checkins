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

document.getElementById('archiveSingleDate').addEventListener('input', async function() {
    document.getElementById('filterManager').value = defaultManagerFilter;
    document.getElementById('filterIC').value = '---';
    archiveData = await getArchiveData();
    buildTableArchive(archiveData);
});

document.getElementById('archiveFromDate').addEventListener('input', async function() {
    document.getElementById('filterManager').value = defaultManagerFilter;
    document.getElementById('filterIC').value = '---';

    const fromDate = new Date(this.value);
    const toDate = new Date(document.getElementById("archiveToDate").value);
    if(toDate > fromDate) {
        archiveData = await getArchiveData();
        buildTableArchive(archiveData);
    }
});

document.getElementById('archiveToDate').addEventListener('input', async function() {
    document.getElementById('filterManager').value = defaultManagerFilter;
    document.getElementById('filterIC').value = '---';

    const fromDate = new Date(document.getElementById("archiveFromDate").value);
    const toDate = new Date(this.value);
    if(toDate > fromDate) {
        archiveData = await getArchiveData();
        buildTableArchive(archiveData);
    }
});

async function getArchiveData() {
    let response;
    if(document.getElementById('singleDateButton').checked) { //single date
        response = await fetch(
            '/php/getDataArchive.php?key=' + accessKey +
            '&archiveSingleDate=' + document.getElementById('archiveSingleDate').value
        );
    } else { //range
        response = await fetch(
            '/php/getDataArchive.php?key=' + accessKey +
            '&archiveFromDate=' + document.getElementById('archiveFromDate').value +
            '&archiveToDate=' + document.getElementById('archiveToDate').value
        );
    }
    
    const data = await response.json();
    return data;
}