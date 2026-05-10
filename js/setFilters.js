document.getElementById('filterManager').addEventListener('input', function () {
    buildTable();
});
document.getElementById('filterIC').addEventListener('input', function () {
    buildTable();
});
document.getElementById('todayOnly').addEventListener('input', function () {
    buildTable();
});

document.getElementById('searchBuildingsForm').addEventListener('submit', function(event) {
    event.preventDefault();
    buildTable();
});
document.getElementById('showActive').addEventListener('input', function (e) {
    const activateButton = document.getElementById('activateButton');
    const deactivateButton = document.getElementById('deactivateButton');
    if(document.getElementById('showActive').checked) {
        activateButton.style.display = 'none';
        deactivateButton.style.display = 'block';
    } else {
        activateButton.style.display = 'block';
        deactivateButton.style.display = 'none';
    }
    buildTable();
});