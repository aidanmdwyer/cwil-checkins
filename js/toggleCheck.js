function toggleCheck(name, checked) {

    if(confirm('Are you sure you want to ' + ((checked === 0) ? 'check' : 'uncheck') + ' ' + name + '?')) {
        refreshButton.disabled = true;
        refreshButton.innerHTML = 'Loading';
        fetch('/php/toggleCheck.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `name=${encodeURIComponent(name)}&checked=${encodeURIComponent(checked)}`
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