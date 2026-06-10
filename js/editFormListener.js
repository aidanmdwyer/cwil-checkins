document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault(); //Prevent default form submit

    const formData = new FormData(e.target);

    fetch('php/editRow.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && resultMessage) {
                resultMessage.style.color = 'green';
                resultMessage.innerHTML = data.message;
                buildTable(undefined, true, false);
            } else if(data.status === 'info') {
                resultMessage.style.color = 'darkorange';
                resultMessage.innerHTML = data.message;
            } else {
                resultMessage.style.color = 'red';
                resultMessage.innerHTML = data.message;
            }
        })
        .catch(err => {
            console.error('Error:', err);
        });
});