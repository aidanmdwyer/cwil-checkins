function scrollRight() {
    window.scrollTo({
        left: document.body.scrollWidth,
        top: window.scrollY,
        behavior: 'smooth'
    });
}

function openQr(rowData) {
    cancel();
    qrForm.style.display = 'block';
    qrPreviewTitle.textContent = rowData['name'];
    qrPreviewIC.textContent = rowData['ic'];
    generateQR(rowData);

    scrollRight();
}

function editRow(rowData) {

    const clone = document.getElementById('filterManager').cloneNode(true);
    clone.id = 'Manager';
    clone.name = 'manager';
    document.getElementById('Manager').parentNode.replaceChild(clone, document.getElementById('Manager'));

    cancel();
    editForm.style.display = 'inline-block';
    document.getElementById('editName').innerHTML = 'Name: ' + rowData['name'];
    document.getElementById('editNameInput').value = rowData['name'];
    document.getElementById('Manager').value = rowData['manager'];
    document.getElementById('Contractor').value = rowData['ic'];
    document.getElementById('monday').checked = (rowData['monday'] == 1);
    document.getElementById('tuesday').checked = (rowData['tuesday'] == 1);
    document.getElementById('wednesday').checked = (rowData['wednesday'] == 1);
    document.getElementById('thursday').checked = (rowData['thursday'] == 1);
    document.getElementById('friday').checked = (rowData['friday'] == 1);
    document.getElementById('saturday').checked = (rowData['saturday'] == 1);
    document.getElementById('sunday').checked = (rowData['sunday'] == 1);
    document.getElementById('MWFCheckbox').checked = false;
    document.getElementById('MFCheckbox').checked = false;
    document.getElementById('allDaysCheckbox').checked = false;

    scrollRight();
}

function cancel() {
    document.getElementById('Manager').value = '';
    document.getElementById('Contractor').value = '';
    editForm.style.display = 'none';
    qrForm.style.display = 'none';
    selectSubmits.style.display = 'none';
    tableSelectedForm.reset();
    if(resultMessage) resultMessage.innerHTML = '';
    if(changeManagerDiv) changeManagerDiv.style.display = 'none';
    if(changeICDiv) changeICDiv.style.display = 'none';
    if(editAllMessage) editAllMessage.innerHTML = '';
    selectedNames = [];
}