function exportBuildingsSheet(tableId, fileName = "buildings.xlsx", metadata = {}) {
    let table = document.getElementById(tableId);
    if(table.rows.length > 0) {
        sheetName = "Sheet1";

        let wb = XLSX.utils.table_to_book(table, {sheet: sheetName});
        const ws = wb.Sheets[sheetName];

        removeColumns(ws, ["", "ID", "QR", "Edit", "Audit", "Last Audit"]);

        autoFitColumn(ws, "Building Name");
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

        writeMetadata(ws, metadata);

        // enable cellStyles so alignment works
        XLSX.writeFile(wb, fileName, {bookType: "xlsx", cellStyles: true});
    }
}

function exportContractorSheet(tableId, fileName = "buildings.xlsx") {
    let table = document.getElementById(tableId);
    if(table.rows.length > 0) {
        // Clone the table so the actual page remains unchanged
        let exportTable = table.cloneNode(true);

        // Format the Associated Emails column
        const headers = Array.from(exportTable.rows[0].cells);
        const emailColumnIndex = headers.findIndex(
            cell => cell.textContent.trim() === "Associated Emails"
        );

        if (emailColumnIndex !== -1) {
            for (let row of Array.from(exportTable.rows).slice(1)) {
                const cell = row.cells[emailColumnIndex];
                if (!cell) continue;

                // Extract emails, ignoring buttons and other elements
                const emails = Array.from(cell.getElementsByClassName("emailsListItem"))
                    .map(div => div.textContent.trim())
                    .filter(text => text.includes("@"));

                cell.textContent = emails.join(", ");
            }
        }

        let wb = XLSX.utils.table_to_book(exportTable, {sheet: "Sheet1"});
        const ws = wb.Sheets["Sheet1"];

        removeColumns(ws, ["Account Link", "Password Reset Link", "Delete"]);

        autoFitColumn(ws, "Contractor Name");
        autoFitColumn(ws, "Status");
        autoFitColumn(ws, "Associated Emails");

        // enable cellStyles so alignment works
        XLSX.writeFile(wb, fileName, {bookType: "xlsx", cellStyles: true});
    }
}

function writeMetadata(ws, metadata) {
    const entries = Object.entries(metadata).filter(([key]) => key !== "sheetName");
    if (entries.length === 0) return;

    const range = XLSX.utils.decode_range(ws['!ref']);
    const startCol = range.e.c + 3; // one empty column gap
    const startRow = range.s.r;     // align with table's header row

    let maxKeyWidth = 10;
    let maxValWidth = 10;

    entries.forEach(([key, value], i) => {
        const row = startRow + i;

        const keyAddr = XLSX.utils.encode_cell({ r: row, c: startCol });
        ws[keyAddr] = { t: "s", v: key };
        maxKeyWidth = Math.max(maxKeyWidth, key.toString().length);

        const valAddr = XLSX.utils.encode_cell({ r: row, c: startCol + 1 });
        const valStr = value.toString();
        if (typeof value === "number") {
            ws[valAddr] = { t: "n", v: value };
        } else {
            ws[valAddr] = { t: "s", v: valStr };
        }
        maxValWidth = Math.max(maxValWidth, valStr.length);
    });

    // Expand the sheet range so the new columns/rows are included in output
    const newRange = {
        s: { r: Math.min(range.s.r, startRow), c: Math.min(range.s.c, startCol) },
        e: {
            r: Math.max(range.e.r, startRow + entries.length - 1),
            c: Math.max(range.e.c, startCol + 1)
        }
    };
    ws['!ref'] = XLSX.utils.encode_range(newRange);

    // Auto-fit the key/value columns
    if (!ws['!cols']) ws['!cols'] = [];
    ws['!cols'][startCol] = { wch: maxKeyWidth };
    ws['!cols'][startCol + 1] = { wch: maxValWidth };
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

function autoFitAllColumns(ws, margin = 0) {
    if (!ws["!ref"]) return;

    const range = XLSX.utils.decode_range(ws["!ref"]);

    ws["!cols"] ??= [];

    for (let C = range.s.c; C <= range.e.c; ++C) {
        let maxWidth = 10;
        let hasContent = false;

        for (let R = range.s.r; R <= range.e.r; ++R) {
            const cellAddr = XLSX.utils.encode_cell({ r: R, c: C });
            const cell = ws[cellAddr];

            if (!cell || cell.v == null || cell.v === "") continue;

            const text = cell.v.toString();
            maxWidth = Math.max(maxWidth, text.length);
            hasContent = true;
        }

        // Only adjust columns that contain content
        if (hasContent) {
            ws["!cols"][C] = {
                ...ws["!cols"][C],
                wch: maxWidth + margin
            };
        }
    }
}






function exportAuditTables(fileName = "export.xlsx") {
    // CONFIGURABLE IMAGE HEIGHT (pixels)
    const IMAGE_HEIGHT = 165;

    const wb = XLSX.utils.book_new();
    const tables = Array.from(document.getElementsByClassName("auditTable"));

    let ws = XLSX.utils.aoa_to_sheet([]);
    let nextRow = 0;
    const images = [];

    // Build worksheet and collect images
    tables.forEach((table) => {
        const rows = Array.from(table.querySelectorAll("tr"));

        // Find the h2 immediately preceding this table
        const heading = table.previousElementSibling;
        const hasHeading = heading?.tagName === "H2";

        // Add section heading
        if (hasHeading) {
            XLSX.utils.sheet_add_aoa(
                ws,
                [[heading.textContent.trim()]],
                { origin: { r: nextRow, c: 0 } }
            );
        }

        const startRow = nextRow + (hasHeading ? 1 : 0);

        XLSX.utils.sheet_add_dom(ws, table, {
            origin: { r: startRow, c: 0 }
        });

        const headers = Array.from(rows[0]?.cells || [])
            .map(cell => cell.textContent.trim());

        const imageColumn = headers.indexOf("Image");

        if (imageColumn !== -1) {
            rows.forEach((row, rowIndex) => {
                const cell = row.cells[imageColumn];
                const button = cell?.querySelector("button");

                if (!button) return;

                const address = XLSX.utils.encode_cell({
                    r: startRow + rowIndex,
                    c: imageColumn
                });

                // Remove "View" regardless of whether an image exists
                delete ws[address];

                // Only embed images for visible buttons
                if (
                    getComputedStyle(button).display !== "none" &&
                    button.dataset.imgSrc
                ) {
                    const src = button.dataset.imgSrc.replace(/\s/g, "");

                    const match =
                        /^data:image\/(jpeg|jpg|png);base64,([\s\S]+)$/i.exec(src);

                    if (!match) {
                        console.warn("Unsupported image format:", button.dataset.imgSrc);
                        return;
                    }

                    images.push({
                        row: startRow + rowIndex,
                        col: imageColumn,
                        extension: match[1].toLowerCase() === "png" ? "png" : "jpeg",
                        base64: match[2]
                    });

                    // Give image rows enough height
                    ws["!rows"] ??= [];
                    ws["!rows"][startRow + rowIndex] = {
                        ...ws["!rows"][startRow + rowIndex],
                        hpt: (IMAGE_HEIGHT + 10) * 0.75
                    };
                }
            });
        }

        // One blank row between tables
        nextRow = startRow + rows.length + 1;
    });

    if (tables.length === 0) return;

    autoFitAllColumns(ws);

    let metadata = {};
    metadata['Date'] = document.getElementById("displayDate").innerText;
    metadata['Time'] = document.getElementById("displayTime").innerText;
    metadata['Auditor'] = document.getElementById("displayAuditor").innerText;
    metadata['Contractor'] = document.getElementById("displayIC").innerText;
    metadata['Manager'] = document.getElementById("displayManager").innerText;
    document.getElementById("displayValues").innerHTML.split("<br>").forEach((item) => {
        const parts = item.split(" ");
        metadata[parts[0]] = parts[1];
    });

    writeMetadata(ws, metadata);

    XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

    // Write workbook, then modify its ZIP contents
    const workbookData = XLSX.write(wb, {
        bookType: "xlsx",
        type: "array"
    });

    addImagesToWorkbook(
        workbookData,
        images,
        fileName,
        IMAGE_HEIGHT
    );
}




// Add actual images to the XLSX ZIP archive

async function addImagesToWorkbook(
    workbookData,
    images,
    fileName,
    imageHeight
) {
    if (images.length === 0) {
        XLSX.writeFile(
            XLSX.read(workbookData, { type: "array" }),
            fileName
        );
        return;
    }

    const zip = await JSZip.loadAsync(workbookData);

    const SHEET_NS =
        "http://schemas.openxmlformats.org/spreadsheetml/2006/main";

    const REL_NS =
        "http://schemas.openxmlformats.org/officeDocument/2006/relationships";

    const PKG_REL_NS =
        "http://schemas.openxmlformats.org/package/2006/relationships";

    const CONTENT_NS =
        "http://schemas.openxmlformats.org/package/2006/content-types";

    const DRAWING_NS =
        "http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing";

    const DRAWING_MAIN_NS =
        "http://schemas.openxmlformats.org/drawingml/2006/main";

    const parser = new DOMParser();
    const serializer = new XMLSerializer();

    function parseXml(xml) {
        const doc = parser.parseFromString(xml, "application/xml");

        if (doc.getElementsByTagName("parsererror").length) {
            throw new Error("Invalid XML in workbook.");
        }

        return doc;
    }

    function addRelationship(doc, id, type, target) {
        const rel = doc.createElementNS(
            PKG_REL_NS,
            "Relationship"
        );

        rel.setAttribute("Id", id);
        rel.setAttribute("Type", type);
        rel.setAttribute("Target", target);

        doc.documentElement.appendChild(rel);
    }

    // Read worksheet XML
    const sheetPath = "xl/worksheets/sheet1.xml";

    const sheetDoc = parseXml(
        await zip.file(sheetPath).async("string")
    );

    const sheetRoot = sheetDoc.documentElement;

    // Set vertical alignment to top for all cells
    const stylesPath = "xl/styles.xml";

    const stylesDoc = parseXml(
        await zip.file(stylesPath).async("string")
    );

    const STYLES_NS =
        "http://schemas.openxmlformats.org/spreadsheetml/2006/main";

    const cellXfs = stylesDoc.getElementsByTagNameNS(
        STYLES_NS,
        "cellXfs"
    )[0];

    Array.from(cellXfs.children).forEach(xf => {
        xf.setAttribute("applyAlignment", "1");

        let alignment = xf.getElementsByTagNameNS(
            STYLES_NS,
            "alignment"
        )[0];

        if (!alignment) {
            alignment = stylesDoc.createElementNS(
                STYLES_NS,
                "alignment"
            );
            xf.appendChild(alignment);
        }

        alignment.setAttribute("vertical", "top");
    });

    zip.file(
        stylesPath,
        serializer.serializeToString(stylesDoc)
    );

    // Read or create worksheet relationships
    const sheetRelsPath =
        "xl/worksheets/_rels/sheet1.xml.rels";

    let sheetRelsDoc;

    if (zip.file(sheetRelsPath)) {
        sheetRelsDoc = parseXml(
            await zip.file(sheetRelsPath).async("string")
        );
    } else {
        sheetRelsDoc = document.implementation.createDocument(
            PKG_REL_NS,
            "Relationships",
            null
        );
    }

    // Find an unused relationship ID
    function nextRelationshipId(doc) {
        const ids = Array.from(
            doc.getElementsByTagNameNS(
                PKG_REL_NS,
                "Relationship"
            )
        ).map(rel => {
            const match = /^rId(\d+)$/.exec(
                rel.getAttribute("Id")
            );

            return match ? Number(match[1]) : 0;
        });

        return "rId" + (Math.max(0, ...ids) + 1);
    }

    const drawingRelId = nextRelationshipId(sheetRelsDoc);

    addRelationship(
        sheetRelsDoc,
        drawingRelId,
        REL_NS + "/drawing",
        "../drawings/drawing1.xml"
    );

    // Attach drawing relationship to worksheet
    if (!sheetRoot.lookupNamespaceURI("r")) {
        sheetRoot.setAttributeNS(
            "http://www.w3.org/2000/xmlns/",
            "xmlns:r",
            REL_NS
        );
    }

    const drawingElement = sheetDoc.createElementNS(
        SHEET_NS,
        "drawing"
    );

    drawingElement.setAttributeNS(
        REL_NS,
        "r:id",
        drawingRelId
    );

    const extLst = sheetRoot.getElementsByTagNameNS(
        SHEET_NS,
        "extLst"
    )[0];

    sheetRoot.insertBefore(drawingElement, extLst || null);

    // Convert configured image height to EMUs
    const heightEMU = imageHeight * 9525;

    // Create image relationships and drawing anchors
    const drawingRels = [];
    const drawingAnchors = [];

    for (const image of images) {
        const imageId = drawingAnchors.length + 1;
        const relationshipId = "rId" + imageId;

        const extension = image.extension;
        const imagePath = `xl/media/image${imageId}.${extension}`;

        // Decode and save image
        const binary = atob(image.base64);
        const bytes = new Uint8Array(binary.length);

        for (let j = 0; j < binary.length; j++) {
            bytes[j] = binary.charCodeAt(j);
        }

        const mime = extension === "png"
            ? "image/png"
            : "image/jpeg";

        // Determine the original image dimensions
        const imageBlob = new Blob([bytes], { type: mime });
        const imageUrl = URL.createObjectURL(imageBlob);

        let dimensions;

        try {
            dimensions = await new Promise((resolve, reject) => {
                const img = new Image();

                img.onload = () => {
                    resolve({
                        width: img.naturalWidth,
                        height: img.naturalHeight
                    });
                };

                img.onerror = () => {
                    reject(new Error("Could not determine image dimensions."));
                };

                img.src = imageUrl;
            });
        } finally {
            URL.revokeObjectURL(imageUrl);
        }

        // Calculate width based on original aspect ratio
        const widthEMU = (
            imageHeight * dimensions.width / dimensions.height
        ) * 9525;

        zip.file(imagePath, bytes);

        drawingRels.push(`
            <Relationship
                Id="${relationshipId}"
                Type="${REL_NS}/image"
                Target="../media/image${imageId}.${extension}"
            />
        `);

        // Position image in its corresponding Excel cell
        drawingAnchors.push(`
            <xdr:oneCellAnchor>
                <xdr:from>
                    <xdr:col>${image.col}</xdr:col>
                    <xdr:colOff>0</xdr:colOff>
                    <xdr:row>${image.row}</xdr:row>
                    <xdr:rowOff>0</xdr:rowOff>
                </xdr:from>

                <xdr:ext cx="${widthEMU}" cy="${heightEMU}"/>

                <xdr:pic>
                    <xdr:nvPicPr>
                        <xdr:cNvPr
                            id="${imageId}"
                            name="Audit Image ${imageId}"
                        />
                        <xdr:cNvPicPr>
                            <a:picLocks noChangeAspect="1"/>
                        </xdr:cNvPicPr>
                    </xdr:nvPicPr>

                    <xdr:blipFill>
                        <a:blip r:embed="${relationshipId}"/>
                        <a:stretch>
                            <a:fillRect/>
                        </a:stretch>
                    </xdr:blipFill>

                    <xdr:spPr>
                        <a:prstGeom prst="rect">
                            <a:avLst/>
                        </a:prstGeom>
                    </xdr:spPr>
                </xdr:pic>

                <xdr:clientData/>
            </xdr:oneCellAnchor>
        `);
    }

    // Create drawing XML
    const drawingXml = `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <xdr:wsDr
            xmlns:xdr="${DRAWING_NS}"
            xmlns:a="${DRAWING_MAIN_NS}"
            xmlns:r="${REL_NS}">
            ${drawingAnchors.join("")}
        </xdr:wsDr>`;

    zip.file("xl/drawings/drawing1.xml", drawingXml);

    // Create drawing relationships
    const drawingRelsXml = `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <Relationships xmlns="${PKG_REL_NS}">
            ${drawingRels.join("")}
        </Relationships>`;

    zip.file(
        "xl/drawings/_rels/drawing1.xml.rels",
        drawingRelsXml
    );

    // Update worksheet relationships
    zip.file(
        sheetRelsPath,
        serializer.serializeToString(sheetRelsDoc)
    );

    // Update worksheet XML
    zip.file(
        sheetPath,
        serializer.serializeToString(sheetDoc)
    );

    // Update [Content_Types].xml
    const contentTypesPath = "[Content_Types].xml";

    const contentDoc = parseXml(
        await zip.file(contentTypesPath).async("string")
    );

    const contentRoot = contentDoc.documentElement;

    function hasDefault(extension) {
        return Array.from(
            contentRoot.getElementsByTagNameNS(
                CONTENT_NS,
                "Default"
            )
        ).some(el => el.getAttribute("Extension") === extension);
    }

    function addDefault(extension, contentType) {
        if (hasDefault(extension)) return;

        const element = contentDoc.createElementNS(
            CONTENT_NS,
            "Default"
        );

        element.setAttribute("Extension", extension);
        element.setAttribute("ContentType", contentType);

        contentRoot.appendChild(element);
    }

    addDefault("jpeg", "image/jpeg");
    addDefault("png", "image/png");

    const drawingOverride = contentDoc.createElementNS(
        CONTENT_NS,
        "Override"
    );

    drawingOverride.setAttribute(
        "PartName",
        "/xl/drawings/drawing1.xml"
    );

    drawingOverride.setAttribute(
        "ContentType",
        "application/vnd.openxmlformats-officedocument.drawing+xml"
    );

    contentRoot.appendChild(drawingOverride);

    zip.file(
        contentTypesPath,
        serializer.serializeToString(contentDoc)
    );

    // Generate and download finished workbook
    const blob = await zip.generateAsync({
        type: "blob",
        compression: "DEFLATE",
        compressionOptions: { level: 6 }
    });

    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");

    link.href = url;
    link.download = fileName;

    document.body.appendChild(link);
    link.click();
    link.remove();

    setTimeout(() => URL.revokeObjectURL(url), 1000);
}
