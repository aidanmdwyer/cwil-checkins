function handleSelect() {
    qrForm.style.display = 'none';
    selectSubmits.style.display = 'inline-block';
    editForm.style.display = 'none';
    let checkboxes = document.getElementById('tableSelectedForm').querySelectorAll(".selectBox");
    selectedIds = [];
    Array.from(checkboxes).forEach(box => {
        if(box.checked) {
            selectedIds.push(box.value);
        }
    });
    if(selectedIds.length > 0) {
        selectedBuildingsText.innerHTML = selectedIds.length + ' buildings selected.';
    } else {
        cancel();
    }
}

function handleSelectAll(isSelected) {
    let checkboxes = document.getElementById('tableSelectedForm').querySelectorAll(".selectBox");
    checkboxes.forEach(box => {
        box.checked = isSelected;
    });
    if(isSelected) {
        handleSelect();
    } else {
        cancel();
    }
}