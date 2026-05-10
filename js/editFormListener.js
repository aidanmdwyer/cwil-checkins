document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault(); //Prevent default form submit

    const formData = new FormData(e.target);

    fetch('php/editRow.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('resultMessage');
            if (data.status === 'success') {
                resultDiv.style.color = 'green';
                resultDiv.innerHTML = data.message;
                buildTable(undefined, true, false);
            } else if(data.status === 'info') {
                resultDiv.style.color = 'darkorange';
                resultDiv.innerHTML = data.message;
            } else {
                resultDiv.style.color = 'red';
                resultDiv.innerHTML = data.message;
            }
        })
        .catch(err => {
            console.error('Error:', err);
        });
});