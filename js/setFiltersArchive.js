const singleDateButton = document.getElementById("singleDateButton");
const dateRangeButton = document.getElementById("dateRangeButton");
const archiveFromDateLabel = document.getElementById("archiveFromDateLabel");
const archiveToDateLabel = document.getElementById("archiveToDateLabel");
const archiveSingleDateLabel = document.getElementById("archiveSingleDateLabel");
const archiveFromDate = document.getElementById("archiveFromDate");
const archiveToDate = document.getElementById("archiveToDate");
const archiveSingleDate = document.getElementById("archiveSingleDate");
const filterManager = document.getElementById('filterManager');
const filterIc = document.getElementById('filterIC');

singleDateButton.addEventListener('input', function() {
    archiveFromDateLabel.style.display = "none";
    archiveToDateLabel.style.display = "none";
    archiveSingleDateLabel.style.display = "flex";
    archiveFromDate.value = "";
    archiveToDate.value = "";
    filterManager.value = defaultManagerFilter;
    filterIc.value = '---';
    resetArchiveTable();
    hideArchiveFilters();
});
dateRangeButton.addEventListener('input', function() {
    archiveFromDateLabel.style.display = "flex";
    archiveToDateLabel.style.display = "flex";
    archiveSingleDateLabel.style.display = "none";
    archiveSingleDate.value = "";
    filterManager.value = defaultManagerFilter;
    filterIc.value = '---';
    resetArchiveTable();
    hideArchiveFilters();
});

filterManager.addEventListener('input', function() {
    buildTableArchive(archiveData);
});

filterIc.addEventListener('input', function() {
    buildTableArchive(archiveData);
});

archiveSingleDate.addEventListener('input', async function() {
    filterManager.value = defaultManagerFilter;
    filterIc.value = '---';
    archiveData = await getArchiveData();
    buildTableArchive(archiveData);
});

archiveFromDate.addEventListener('input', async function() {
    filterManager.value = defaultManagerFilter;
    filterIc.value = '---';

    archiveToDate.disabled = false;
    archiveToDate.min = archiveFromDate.value;
    const maxDate = new Date(archiveFromDate.value);
    maxDate.setDate(maxDate.getDate() + 30);
    archiveToDate.max = maxDate.toISOString().split('T')[0];
    archiveToDate.value = archiveFromDate.value;
});

archiveToDate.addEventListener('input', async function() {
    filterManager.value = defaultManagerFilter;
    filterIc.value = '---';
    if(archiveFromDate.value) {
        archiveData = await getArchiveData();
        buildTableArchive(archiveData);
    }
});

async function getArchiveData() {
    let response;
    if(singleDateButton.checked) { //single date
        response = await fetch(
            '/php/getDataArchive.php?key=' + accessKey +
            '&archiveSingleDate=' + archiveSingleDate.value
        );
    } else { //range
        response = await fetch(
            '/php/getDataArchive.php?key=' + accessKey +
            '&archiveFromDate=' + archiveFromDate.value +
            '&archiveToDate=' + archiveToDate.value
        );
    }
    
    const data = await response.json();
    return data;
}