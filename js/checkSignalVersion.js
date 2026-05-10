let lastVersion = 0;

setInterval(() => {
    fetch('/php/getSignalVersion.php')
        .then(r => r.text())
        .then(version => {
            version = parseInt(version, 10);
            if (version !== lastVersion) {
                lastVersion = version;
                // refresh table or reload
                buildTable(undefined, true, false, selectedIds, false);
            }
        });
}, 10000);