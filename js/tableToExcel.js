function exportBuildingsSheet(tableId, fileName = "buildings.xlsx") {
    let table = document.getElementById(tableId);
    if(table.rows.length > 0) {
        let wb = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});
        const ws = wb.Sheets["Sheet1"];

        removeColumns(ws, ["", "ID", "QR", "Edit"]);

        autoFitColumn(ws, "Name");
        autoFitColumn(ws, "Manager");
        autoFitColumn(ws, "IC");
        autoFitColumn(ws, "✅", -5);
        autoFitColumn(ws, "Time Checked");
        autoFitColumn(ws, "M", -5);
        autoFitColumn(ws, "T", -5);
        autoFitColumn(ws, "W", -5);
        autoFitColumn(ws, "Th", -5);
        autoFitColumn(ws, "F", -5);
        autoFitColumn(ws, "Sat", -5);
        autoFitColumn(ws, "Sun", -5);

        // enable cellStyles so alignment works
        XLSX.writeFile(wb, fileName, {bookType: "xlsx", cellStyles: true});
    }
}

function exportContractorSheet(tableId, fileName = "buildings.xlsx") {
    let table = document.getElementById(tableId);
    if(table.rows.length > 0) {
        let wb = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});
        const ws = wb.Sheets["Sheet1"];

        removeColumns(ws, ["Account Link", "Password Reset Link", "Delete"]);

        autoFitColumn(ws, "Contractor Name");
        autoFitColumn(ws, "Status");

        // enable cellStyles so alignment works
        XLSX.writeFile(wb, fileName, {bookType: "xlsx", cellStyles: true});
    }
}

// columns are 0-indexed (A=0, B=1, C=2, etc.)
function removeColumns(ws, headersToRemove) {
    const range = XLSX.utils.decode_range(ws['!ref']);

    // 1. Read the header row (first row)
    const headerNames = [];
    for (let C = range.s.c; C <= range.e.c; ++C) {
        const cellAddress = XLSX.utils.encode_cell({ r: range.s.r, c: C });
        const cell = ws[cellAddress];
        const header = cell ? cell.v.toString().trim() : "";
        headerNames.push(header);
    }

    // 2. Find column indices that match the names to remove
    const colsToRemove = [];
    headersToRemove.forEach(headerName => {
        const idx = headerNames.findIndex(
            h => h.toLowerCase() === headerName.toLowerCase()
        );
        if (idx >= 0) colsToRemove.push(idx);
    });

    // 3. If nothing to remove, stop early
    if (colsToRemove.length === 0) return;

    // 4. Remove columns (right-to-left to avoid shifting)
    colsToRemove.sort((a, b) => b - a);
    colsToRemove.forEach(colIndex => {
        for (let R = range.s.r; R <= range.e.r; ++R) {
            const cellAddress = XLSX.utils.encode_cell({ r: R, c: colIndex });
            delete ws[cellAddress];
        }
        for (let C = colIndex + 1; C <= range.e.c; ++C) {
            for (let R = range.s.r; R <= range.e.r; ++R) {
                const from = XLSX.utils.encode_cell({ r: R, c: C });
                const to = XLSX.utils.encode_cell({ r: R, c: C - 1 });
                if (ws[from]) ws[to] = ws[from];
                else delete ws[to];
            }
        }
        range.e.c--;
        ws['!ref'] = XLSX.utils.encode_range(range);
    });
}

function autoFitColumn(ws, columnHeader, margin = 0) {
    const range = XLSX.utils.decode_range(ws['!ref']);

    // Find which column index matches the given header
    let targetCol = -1;
    for (let C = range.s.c; C <= range.e.c; ++C) {
        const cellAddr = XLSX.utils.encode_cell({ r: range.s.r, c: C });
        const cell = ws[cellAddr];
        const header = cell ? cell.v.toString().trim().toLowerCase() : "";
        if (header === columnHeader.toLowerCase()) {
            targetCol = C;
            break;
        }
    }

    if (targetCol === -1) {
        console.warn(`Column "${columnHeader}" not found`);
        return;
    }

    // Calculate the max text length for that column
    let maxWidth = 10;
    for (let R = range.s.r; R <= range.e.r; ++R) {
        const cellAddr = XLSX.utils.encode_cell({ r: R, c: targetCol });
        const cell = ws[cellAddr];
        if (!cell || !cell.v) continue;
        const text = cell.v.toString();
        maxWidth = Math.max(maxWidth, text.length);
    }

    // Add margin (in "wch" units = roughly number of characters)
    const widthWithMargin = maxWidth + margin;

    // Ensure !cols array exists
    if (!ws['!cols']) ws['!cols'] = [];
    ws['!cols'][targetCol] = { wch: widthWithMargin };
}