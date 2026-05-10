function copyAccountLink(name, accountType, element) {
    const text = window.location.origin + '/php/createAccount.php?username=' + encodeURIComponent(name) + "&accountType=" + encodeURIComponent(accountType);
    copyText(text, element);
}

function copyLoginLink(name, accountType, element) {
    name = decodeURIComponent(name);
    const text = window.location.origin + '?username=' + encodeURIComponent(name) + "&accountType=" + encodeURIComponent(accountType);
    copyText(text, element);
}

function copyResetLink(name, element) {
    if (element.classList.contains('isDisabled')) {
        return;
    }
    name = decodeURIComponent(name);
    const text = window.location.origin + '/php/resetPassword.php?username=' + encodeURIComponent(name);

    navigator.clipboard.writeText(text).then(function() {
        element.classList.add('isDisabled');
        element.innerHTML = '&#9989 copied';

        fetch('/php/updateResetTimer.php?key=' + accessKey, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username: name })
        })
            .then(res => res.json())
            .then(data => {
                // ✅ Update just the status cell in the same row
                const row = element.closest("tr");
                if (row) {
                    const statusCell = row.querySelector("td:nth-child(4)"); // adjust index if needed
                    if (statusCell) {
                        statusCell.textContent = "24h until link expires";
                        statusCell.style.color = "green";
                    }
                }
            })
            .catch(err => {
                console.error('Error updating reset timer:', err);
            });

        setTimeout(() => {
            element.innerHTML = '&#128260';
            element.classList.remove('isDisabled');
        }, 1500);
    });
}

function copyText(text, element) {
    if (element.classList.contains('isDisabled')) {
        return;
    }

    navigator.clipboard.writeText(text).then(function() {
        element.classList.add('isDisabled');

        element.innerHTML = '&#9989 copied';
        setTimeout(() => {
            element.innerHTML = '&#128203';
            element.classList.remove('isDisabled');
        }, 1500);
    });
}

function confirmDelete(button) {
    const form = button.closest('form');
    const name = form.getAttribute('data-name');
    return confirm('Are you sure you want to delete ' + name + '?');
}