async function editAll(event, type) {
    let confirmStr = '';
    switch(type) {
        case 'changeIC':
            confirmStr = 'Are you sure you want to edit ' + selectedNames.length + ' building' + ((selectedNames.length > 1) ? 's?' : '?');
            break;
        case 'changeManager':
            confirmStr = 'Are you sure you want to edit ' + selectedNames.length + ' building' + ((selectedNames.length > 1) ? 's?' : '?');
            break;
        case 'activate':
            confirmStr = 'Are you sure you want to activate ' + selectedNames.length + ' building' + ((selectedNames.length > 1) ? 's?' : '?');
            break;
        case 'deactivate':
            confirmStr = 'Are you sure you want to deactivate ' + selectedNames.length + ' building' + ((selectedNames.length > 1) ? 's?' : '?');
            break;
        case 'checkAll':
            confirmStr = 'Are you sure you want to check ' + selectedNames.length + ' building' + ((selectedNames.length > 1) ? 's?' : '?');
            break;
        case 'uncheckAll':
            confirmStr = 'Are you sure you want to uncheck ' + selectedNames.length + ' building' + ((selectedNames.length > 1) ? 's?' : '?');
            break;
        default:
            console.error('bad input for editAll()');
            return;
    }

    if(confirm(confirmStr)) {
        event.preventDefault();

        const formData = new FormData(document.getElementById('tableSelectedForm'));
        formData.append('action', type);

        const res = await fetch('/php/handleSelect.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (type === 'activate' || type === 'deactivate') {
            cancel();
        } else if(editAllMessage) {
            let messageColor = 'black';
            switch (data['status']) {
                case 'success':
                    messageColor = 'green';
                    break;
                case 'error':
                    messageColor = 'red';
                    break;
                case 'info':
                    messageColor = 'darkorange';
                    break;
            }
            editAllMessage.style.color = messageColor;
            editAllMessage.style.display = 'block';
            editAllMessage.innerText = data['message'];
        }

        buildTable(undefined, true, false, data['updated']);
    }
}

function changeManager(event) {
    event.preventDefault();

    if(changeManagerDiv) changeManagerDiv.style.display = 'block';
    if(changeICDiv) changeICDiv.style.display = 'none';
    resetChangeValues();
}

function changeIC(event) {
    event.preventDefault();

    if(changeManagerDiv) changeManagerDiv.style.display = 'none';
    if(changeICDiv) changeICDiv.style.display = 'block';
    resetChangeValues();
}

function resetChangeValues() {
    document.getElementById('Manager').value = '';
    document.getElementById('Contractor').value = '';
    if(editAllMessage) editAllMessage.innerHTML = '';

    cloneElement('filterManager', 'changeManager', 'changeManager');
    cloneElement('Contractor', 'changeIC', 'changeIC');
    cloneElement('contractorSuggestions', 'changeICSuggestions');
    searchContractors('changeIC', 'changeICSuggestions');
}

function cloneElement(original, cloned, name = null, tableSelectedForm = false) {
    const clone = document.getElementById(original).cloneNode(true);
    clone.id = cloned;
    if (name) {
        clone.name = name;
        clone.setAttribute('form', 'tableSelectedForm');
    }

    document.getElementById(cloned).parentNode.replaceChild(clone, document.getElementById(cloned));
}
