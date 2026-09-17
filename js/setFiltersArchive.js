document.getElementById('filterManager').addEventListener('input', function() {
    buildTableArchive(archiveData);
});

document.getElementById('filterIC').addEventListener('input', function() {
    buildTableArchive(archiveData);
});

document.getElementById('archiveFromDate').addEventListener('input', async () => {
    const archiveToDate = document.getElementById("archiveToDate").value;
    if(!archiveToDate) {
        archiveToDate = this.value;
    }
    archiveData = await getArchiveData();
    document.getElementById('filterManager').value = defaultManagerFilter;
    document.getElementById('filterIC').value = '---';
    buildTableArchive(archiveData);
});

document.getElementById('archiveToDate').addEventListener('input', async () => {
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