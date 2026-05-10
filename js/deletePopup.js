function showDeletePopup(event) {
    event.preventDefault();

    if (document.getElementById('deletePopup')) return;

    const deletePopup = document.createElement('div');
    deletePopup.id = 'deletePopup';
    deletePopup.style.display = 'flex';
    deletePopup.style.position = 'fixed';
    deletePopup.style.top = '0';
    deletePopup.style.left = '0';
    deletePopup.style.width = '100%';
    deletePopup.style.height = '100%';
    deletePopup.style.background = 'rgba(0,0,0,0.5)';
    deletePopup.style.zIndex = '9999';
    deletePopup.style.justifyContent = 'center';
    deletePopup.style.alignItems = 'center';

    deletePopup.innerHTML = `
        <div style="background: white; padding: 10px 20px 20px 20px; border-radius: 8px; width: 300px; text-align: center;">
            <p>Are you sure you want to delete selected building(s)?</p>
            <p>Type <strong>DELETE</strong> to confirm:</p>
            <input type="text" id="deleteInput" style="width: 100%; padding: 8px; box-sizing: border-box;" />
            <div style="margin-top: 10px;">
                <button onclick="closeDeletePopup()">Cancel</button>
                <button onclick="confirmDeleteBuildings()">Confirm</button>
            </div>
        </div>
    `;

    document.body.appendChild(deletePopup);

    const input = document.getElementById('deleteInput');
    input.focus();

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') confirmDeleteBuildings();
    });
}

function closeDeletePopup() {
    const popup = document.getElementById('deletePopup');
    if (popup) popup.remove();
}

async function confirmDeleteBuildings() {
    const input = document.getElementById('deleteInput');
    if (!input) return;

    if (input.value.trim() === 'DELETE') {
        closeDeletePopup();

        const form = document.getElementById('tableSelectedForm');

        // Collect form data
        const formData = new FormData(form);
        formData.append('action', 'delete');
        formData.append('loadAll', document.getElementById('loadAll').style.display === 'none' ? 'true' : 'false');

        // Count selected checkboxes
        const checkboxes = form.querySelectorAll(".selectBox");

        if (selectedIds.length > 1) {
            if (!confirm('You are about to delete ' + selectedIds.length + ' buildings. This action cannot be undone.')) {
                return; // user canceled
            }
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error('Network response was not ok');

            buildTable(undefined, true);

            alert('Selected building(s) deleted successfully!');
        } catch (err) {
            console.error('Error deleting buildings:', err);
            alert('There was an error deleting the buildings.');
        }
    } else {
        alert('You must type DELETE to confirm.');
        input.focus();
    }
}
