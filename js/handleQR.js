function htmlEntities(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function generateQR(data) {
    return new Promise(resolve => {
        qrPreview.innerHTML = '';
        qrContainer.innerHTML = '';

        document.getElementById('printBuildingName').innerHTML = htmlEntities(data.name);

        const observer = new MutationObserver(() => {
            if (qrContainer.querySelector('canvas')) {
                observer.disconnect();
                resolve();
            }
        });

        observer.observe(qrContainer, { childList: true, subtree: true });

        new QRCode(qrPreview, {
            text: window.location.origin + `/php/checkIn.php?id=${data.id}`,
            width: 260,
            height: 260,
        });

        new QRCode(qrContainer, {
            text: window.location.origin + `/php/checkIn.php?id=${data.id}`,
            width: 800,
            height: 800,
            correctLevel: QRCode.CorrectLevel.H,
        });
    });
}

function waitForImagesLoad(doc) {
    const images = Array.from(doc.images);
    if (images.length === 0) return Promise.resolve();
    return Promise.all(images.map(img => {
        if (img.complete) return Promise.resolve();
        return new Promise(res => img.onload = img.onerror = res);
    }));
}

async function printQR() {
    //const todayOnlyChecked = document.getElementById('todayOnlyFilter').value;

    // Clone and prepare the QR template
    const clone = qrTemplate.cloneNode(true);
    clone.style.display = 'flex';

    // Replace the QR canvas with an image (if one exists)
    const qrCanvas = qrContainer.querySelector('canvas');
    if (qrCanvas) {
        const qrImg = `<img src="${qrCanvas.toDataURL('image/png')}" style="width: 2.75in; height: 2.75in; margin-top: 0.15in;">`;
        const cloneQR = clone.querySelector('#qrcode');
        if (cloneQR) cloneQR.outerHTML = qrImg;
    }

    // Create or get the hidden iframe
    let iframe = document.getElementById('printFrame');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.id = 'printFrame';
        document.body.appendChild(iframe);
    }

    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Contractor QR</title>
            <style>
                body { margin: 0; }
            </style>
        </head>
        <body>
            ${clone.outerHTML}
        </body>
        </html>
    `);
    doc.close();

    // Wait for all images to load before printing
    await waitForImagesLoad(doc);

    // Print iframe content
    iframe.contentWindow.focus();
    iframe.contentWindow.print();

    // Redirect to restore state
    // window.location.href = window.location.pathname + "?manager=" +
    //     encodeURIComponent(document.getElementById('managerFilter').value) +
    //     '&todayOnly=' + todayOnlyChecked +
    //     '&loadAll=' + (document.getElementById('loadAll').style.display === 'none' ? 'true' : 'false');
}

async function printAll() {
    const printProgress = document.getElementById('printProgress');
    const printProgressText = document.getElementById('printProgressText');
    printProgress.value = 0;
    printProgressText.textContent = "Loading";

    const formData = new FormData(document.getElementById('tableSelectedForm'));
    formData.append('action', 'print');

    const res = await fetch('/php/handleSelect.php', {
        method: 'POST',
        body: formData
    });
    const data = await res.json();

    const overlay = document.getElementById('printAllOverlay');
    //const todayOnlyChecked = document.getElementById('todayOnlyFilter').value;
    const tableRows = Array.from(buildingsTable.rows);

    overlay.style.display = 'flex';
    await new Promise(r => setTimeout(r, 50)); // Allow browser to repaint overlay

    let fullPrint = '';
    let count = 0;
    let numLoadingDots = 1;

    for (const id of data) {
        printProgress.value = (count / data.length) * 100;
        printProgressText.textContent = "Loading" + ".".repeat(numLoadingDots);

        if (count % 15 === 0) {
            if (numLoadingDots < 3) {
                numLoadingDots += 1;
            } else {
                numLoadingDots = 1;
            }
        }

        await new Promise(r => setTimeout(r, 10));

        const generateData = { id, name: 'ERROR', ic: 'ERROR' };

        let nameIndex = -1;
        let icIndex = -1;
        Array.from(tableRows[0].cells).forEach((cell, index) => {
            const headerText = cell.textContent.trim();
            if (headerText === 'Name') {
                nameIndex = index;
            } else if (headerText === 'IC') {
                icIndex = index;
            }
        });
        if(nameIndex !== -1 && icIndex !== -1) {
            tableRows.forEach(row => {
                if (row.getAttribute('data-id') === id.toString()) {
                    generateData.name = row.cells[nameIndex].textContent;
                    generateData.ic = row.cells[icIndex].textContent;
                }
            });
        } else {
            console.error('No row found');
        }

        await generateQR(generateData);

        const qrCanvas = qrContainer.querySelector('canvas');
        if (!qrCanvas) continue;

        const qrImg = `<img src="${qrCanvas.toDataURL('image/png')}" style="width: 2.75in; height: 2.75in; margin-top: 0.15in;">`;
        const clone = qrTemplate.cloneNode(true);
        clone.style.display = 'flex';

        const cloneQR = clone.querySelector('#qrcode');
        if (cloneQR) cloneQR.outerHTML = qrImg;

        fullPrint += clone.outerHTML;

        count++;
        if (count % 2 === 0) {
            fullPrint += `<div style="page-break-after: always;"></div>`;
        }
    }

    overlay.style.display = 'none';

    // Create or select hidden iframe
    let iframe = document.getElementById('printFrame');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.id = 'printFrame';
        document.body.appendChild(iframe);
    }

    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Contractor QR</title>
            <style>
                body {margin: 0;}
            </style>
        </head>
        <body>
            ${fullPrint}
        </body>
        </html>
    `);
    doc.close();

    // Wait for images to load inside iframe
    await waitForImagesLoad(doc);

    // Focus and print iframe content
    iframe.contentWindow.focus();
    iframe.contentWindow.print();

    // window.location.href = window.location.pathname + "?manager=" +
    //     encodeURIComponent(document.getElementById('managerFilter').value) +
    //     '&todayOnly=' + todayOnlyChecked +
    //     '&loadAll=' + (document.getElementById('loadAll').style.display === 'none' ? 'true' : 'false');
}
