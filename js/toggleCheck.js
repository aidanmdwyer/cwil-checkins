function toggleCheck(id, name, checked) {

    if(confirm('Are you sure you want to ' + ((checked === 0) ? 'check' : 'uncheck') + ' ' + decodeURIComponent(name) + '?')) {
        refreshButton.disabled = true;
        refreshButton.innerHTML = 'Loading';
        fetch('/php/toggleCheck.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${encodeURIComponent(id)}&checked=${encodeURIComponent(checked)}`
        })
            .then(response => response.text())
            .then(result => {
                buildTable(undefined, true, false, selectedNames);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
}