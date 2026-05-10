function handleDayCheck(isChecked) {
    document.getElementById('MWFCheckbox').checked = false;
    document.getElementById('MFCheckbox').checked = false;
    document.getElementById('allDaysCheckbox').checked = false;
}

function handleDayCheckMWF(isChecked) {
    if(isChecked) {
        document.getElementById('monday').checked = true;
        document.getElementById('tuesday').checked = false
        document.getElementById('wednesday').checked = true;
        document.getElementById('thursday').checked = false;
        document.getElementById('friday').checked = true;
        document.getElementById('saturday').checked = false;
        document.getElementById('sunday').checked = false;
        document.getElementById('MFCheckbox').checked = false;
        document.getElementById('allDaysCheckbox').checked = false;

    } else {
        let allBoxes = document.getElementById('dayChecks').querySelectorAll("input[type='checkbox']");
        allBoxes.forEach(box => {
            box.checked = false;
        });
    }
}

function handleDayCheckMF(isChecked) {
    if(isChecked) {
        document.getElementById('monday').checked = true;
        document.getElementById('tuesday').checked = true;
        document.getElementById('wednesday').checked = true;
        document.getElementById('thursday').checked = true;
        document.getElementById('friday').checked = true;
        document.getElementById('saturday').checked = false;
        document.getElementById('sunday').checked = false;
        document.getElementById('MWFCheckbox').checked = false;
        document.getElementById('allDaysCheckbox').checked = false;

    } else {
        let allBoxes = document.getElementById('dayChecks').querySelectorAll("input[type='checkbox']");
        allBoxes.forEach(box => {
            box.checked = false;
        });
    }
}
function handleDayCheckAll(isChecked) {
    document.getElementById('monday').checked = isChecked;
    document.getElementById('tuesday').checked = isChecked
    document.getElementById('wednesday').checked = isChecked;
    document.getElementById('thursday').checked = isChecked;
    document.getElementById('friday').checked = isChecked;
    document.getElementById('saturday').checked = isChecked;
    document.getElementById('sunday').checked = isChecked;
    document.getElementById('MWFCheckbox').checked = false;
    document.getElementById('MFCheckbox').checked = false;

}