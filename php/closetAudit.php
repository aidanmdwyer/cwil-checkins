<?php
include 'login.php';
include 'accountProperties.php';

if (!accountProperties('Perform Closet Audits')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to access this page.');
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') { //regular page load
    $buildingName = $_GET['buildingName'];
    if(!$buildingName) {
        http_response_code(404);
        die('Page load failed.');
    }

    $auditor = $_SESSION['username'];
    $now = new DateTime('now', new DateTimeZone('America/Chicago'));
    $date = $now->format("Y-m-d H:i:s");
    $dateFormatted = $now->format("Y-m-d");

    require_once 'db.php';

    $contractorStmt = $conn->prepare("SELECT ic FROM buildings WHERE name = ?");
    $contractorStmt->bind_param("s", $buildingName);
    $contractorStmt->execute();
    $contractorResults = $contractorStmt->get_result();
    $ic = $contractorResults->fetch_assoc()['ic'];
    if(!$ic) {
        http_response_code(404);
        die('Page load failed.');
    }
    $contractorStmt->close();

    $lastDateStmt = $conn->prepare("SELECT date FROM closet_audits WHERE buildingName = ? ORDER BY date DESC LIMIT 1");
    $lastDateStmt->bind_param("s", $buildingName);
    $lastDateStmt->execute();
    $lastDateResults = $lastDateStmt->get_result();
    if($lastDateResults->num_rows > 0) {
        $lastAuditDateTime = new DateTime(
                $lastDateResults->fetch_assoc()['date']
            );
        $lastAuditDate = $lastAuditDateTime->format('Y-m-d');
    }

    $lastDateStmt->close();

    $conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Closet Audit</title>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/x-icon" href="/imgs/favicon.png">
    <link rel="manifest" href="/manifest.json">
</head>
<body>
    <h1>
        Closet Audit -
        <br><?= $buildingName?>
    </h1>

    <div class='hr'></div>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="buildingName" value="<?=htmlspecialchars($buildingName)?>">

        <h3>Date: <?=htmlspecialchars($dateFormatted)?></h3>
        <input type="hidden" name="date" value="<?=$date?>">
        <br>

        <?php if($lastAuditDate) { ?>
            <h3>Last Audit: <?=htmlspecialchars($lastAuditDate)?></h3>
            <br>
        <?php } ?>

        <h3>Auditor: <?=htmlspecialchars($auditor)?></h3>
        <input type="hidden" name="auditor" value="<?=htmlspecialchars($auditor)?>">
        <br>
        
        <h3>Contractor: <?=htmlspecialchars($ic)?></h3>
        <input type="hidden" name="ic" value="<?=htmlspecialchars($ic)?>">
        <br>

        <?php
        $auditItems = [
            "Closet Condition" => [
                "Closet door secured and functioning properly",
                "Closet is clean and free of debris",
                "Floors are swept and free of spills",
                "Shelving is clean and organized",
                "No unnecessary personal items stored in closet",
                "QR Code properly stored in closet"
            ],
            "Chemical Storage & Safety" => [
                "All chemicals properly labeled",
                "SDS (Safety Data Sheets) available on-site",
                "Chemicals stored upright and secured",
                "No leaking or damaged containers",
                "Chemicals separated appropriately",
                "Spray bottles labeled with contents",
                "Envirox System"
            ],
            "Equipment Condition" => [
                "Vacuum clean and operational",
                "Mop and bucket clean and in good condition",
                "Brooms and dustpans stored properly",
                "Extension cords properly wrapped and stored",
                "Equipment free from excessive wear or damage",
                "Equipment stored neatly and safely"
            ],
            "Inventory & Supplies" => [
                "Brut on cart with apron",
                "Minimum 2 Mop Heads",
                "Vacuum Cleaner",
                "Colored microfibers",
                "Dusting Tool/Dusting Mop",
                "Mop Bucket"
            ],
            "Compliance & Professional Standards" => [
                "Company-approved products being used",
                "No unauthorized chemicals present",
                "PPE available and accessible",
                "PPE being used appropriately",
                "Closet reflects company standards"
            ],
        ];

        echo "<div class='hr'></div>";
        foreach ($auditItems as $section => $list) {
            echo "<h2>$section</h2>";
            echo "<table>";
            echo "<tr>
                <th>❌</th>
                <th>✅</th>
                <th>N/A</th>
                <th>Item</th>
                <th>Comments</th>
                <th>Image</th>
            </tr>";
            foreach ($list as $item) {
                echo "
                <tr>
                    <td class='checkboxCell'>
                        <label>
                            <input type='radio' name='auditItems[$item]' value='0' checked>
                        </label>
                    </td>
                    <td class='checkboxCell'>
                        <label>
                            <input type='radio' name='auditItems[$item]' value='1'>
                        </label>
                    </td>
                    <td class='checkboxCell'>
                        <label>
                            <input type='radio' name='auditItems[$item]' value='2'>
                        </label>
                    </td>
                    <td>
                        $item
                    </td>
                    <td style='padding: 0; margin: 0;'>
                        <textarea name='comments[$item]' placeholder='Comments...' maxlength='160'></textarea>
                    </td>
                    <td class='imageCell'>
                        <input type='file' name='images[]' accept='image/*' class='imageInput'>

                        <button type='button' class='previewButton' style='display: none;'>
                            Preview
                        </button>
                    </td>
                </tr>";
            }
            echo "</table>";
            echo "<br><div class='hr'></div>";
        }
        ?>

        <h2>Upload an Image of the Entire Closet.</h2>
        <div>
            <input type='file' name='closetImage' accept='image/*' class='imageInput' required>
            <button type='button' class='previewButton' style='display: none;'>
                Preview
            </button>
        </div>
        <br>

        <div class="hr"></div>

        <button type="submit" class="big">Submit Audit</button>

    </form>
    <div id="imageOverlay" class="imageOverlay">
        <button type="button" id="closeOverlay">X</button>
        <img id="overlayImage">
    </div>
</body>
<style>
    html {
        width: 100%;
        height: 100%;
    }
    body {
        width: min(100%, 800px);
        margin: 0 auto;
        padding: 0 30px 200px 30px;
        box-sizing: border-box;

        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: center;
        justify-content: flex-start;
    }

    h1 {
        text-align: center;
    }
    h3 {
        margin: 0;
        padding: 0;
    }

    form {
        width: 100%;
        margin: 0;
        padding: 0;
    }
    table {
        width: 100%;
    }
    textarea {
        width: 120px;
        height: 100%;
        resize: none;
        box-sizing: border-box;
        padding: 5px;
        margin: 0;
        outline: none;
        border: none;
        background: transparent;
    }

    .checkboxCell {
        padding: 0;
        position: relative;
    }

    .checkboxCell label {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        inset: 1px;
    }

    .imageOverlay {
        display: none;
        position: fixed;
        z-index: 1000;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;

        background: rgba(0, 0, 0, 0.8);

        justify-content: center;
        align-items: center;
    }
    .imageOverlay img {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
    }
    #closeOverlay {
        position: absolute;
        top: 20px;
        right: 30px;
        font-size: 30px;
        cursor: pointer;
    }

    .imageCell {
        min-width: 100px;
        width: 100px;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .imageCell input[type="file"] {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }

</style>
<script>
async function compressImage(file) {

    const bitmap = await createImageBitmap(file);

    const maxWidth = 1280;
    const maxHeight = 720;

    const scale = Math.min(
        maxWidth / bitmap.width,
        maxHeight / bitmap.height,
        1
    );

    const width = Math.round(bitmap.width * scale);
    const height = Math.round(bitmap.height * scale);

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;

    const ctx = canvas.getContext('2d');

    // White background (JPEG doesn't support transparency)
    ctx.fillStyle = '#FFFFFF';
    ctx.fillRect(0, 0, width, height);

    ctx.drawImage(bitmap, 0, 0, width, height);

    bitmap.close();

    return new Promise(resolve => {
        canvas.toBlob(resolve, 'image/jpeg', 0.82);
    });
}

const imageOverlay = document.getElementById('imageOverlay');
const overlayImage = document.getElementById('overlayImage');
const closeOverlay = document.getElementById('closeOverlay');

let previewURLs = new Map();

document.querySelectorAll('.imageInput').forEach(input => {

    const previewButton = input.parentElement.querySelector('.previewButton');

    input.addEventListener('change', async function() {

        previewButton.style.display = 'none';

        if (previewURLs.has(this)) {
            URL.revokeObjectURL(previewURLs.get(this));
            previewURLs.delete(this);
        }

        const file = this.files[0];

        if (!file) return;

        try {
            const compressedBlob = await compressImage(file);

            const compressedFile = new File(
                [compressedBlob],
                file.name.replace(/\.[^.]+$/, '') + '.jpg',
                {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                }
            );

            // Replace the original file with the compressed one
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(compressedFile);
            this.files = dataTransfer.files;

            // Generate preview URL from compressed image
            previewURLs.set(this, URL.createObjectURL(compressedBlob));

            previewButton.style.display = 'inline-block';

        }
        catch (error) {
            console.error('Image compression failed:', error);
            alert('Unable to process this image.');
            this.value = '';
        }

    });

    previewButton.addEventListener('click', function() {
        overlayImage.src = previewURLs.get(input);
        imageOverlay.style.display = 'flex';
    });

});

closeOverlay.addEventListener('click', function() {
    imageOverlay.style.display = 'none';
    overlayImage.src = '';
});

imageOverlay.addEventListener('click', function(e) {
    if (e.target === imageOverlay) {
        imageOverlay.style.display = 'none';
        overlayImage.src = '';
    }
});

document.querySelectorAll('table tr').forEach(row => {

    const radios = row.querySelectorAll('input[type="radio"]');
    const imageInput = row.querySelector('.imageInput');

    if (radios.length === 0) return;

    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            imageInput.required = this.value === '0';
        });
    });

    // Set initial state
    imageInput.required = row.querySelector('input[type="radio"]:checked').value === '0';

});
</script>
</html>

<?php
} else { //after submission
    $buildingName = $_POST['buildingName'];
    $date = $_POST['date'];
    $auditor = $_POST['auditor'];
    $ic = $_POST['ic'];
    $auditItems = $_POST['auditItems'];
    $auditItemComments = $_POST['comments'];
    $closetImage = null;
    if ($_FILES['closetImage']['error'] === UPLOAD_ERR_OK) {
        $closetImage = file_get_contents($_FILES['closetImage']['tmp_name']);
    }
    $images = $_FILES['images'];

    require_once 'db.php';

    $closetAuditInsertStmt = $conn->prepare("INSERT INTO closet_audits (buildingName, date, auditor, ic, closetImage) VALUES (?, ?, ?, ?, ?);");
    $null = null;
    $closetAuditInsertStmt->bind_param("ssssb", $buildingName, $date, $auditor, $ic, $null);
    if ($closetImage !== null) {
        $closetAuditInsertStmt->send_long_data(4, $closetImage);
    }
    $closetAuditInsertStmt->execute();
    $closetAuditInsertStmt->close();

    $auditId = $conn->insert_id;

    $auditItemsValuesList = [];
    $auditItemsTypeString = "";
    $auditCommentsValuesList = [];
    $auditCommentsTypeString = "";
    $itemId = 0;
    $auditItemCount = 0;
    $auditCommentCount = 0;

    $auditImagesValues = [];
    $auditImages = [];
    $auditImageCount = 0;

    foreach ($auditItems as $item => $value) {
        $auditItemCount++;
        //prep audit items insert
        $auditItemsTypeString .= "iisi";

        $auditItemsValuesList[] = $auditId;
        $auditItemsValuesList[] = $itemId;
        $auditItemsValuesList[] = $item;
        $auditItemsValuesList[] = $value;

        //prep comments insert
        if($auditItemComments[$item]) {
            $auditCommentCount++;

            $auditCommentsTypeString .= "iis";

            $auditCommentsValuesList[] = $auditId;
            $auditCommentsValuesList[] = $itemId;
            $auditCommentsValuesList[] = $auditItemComments[$item];
        }

        // Insert image if one was uploaded for this item
        if ($images['error'][$itemId] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($images['tmp_name'][$itemId]);

            if ($imageData === false) {
                die("Failed to read uploaded image.");
            }

            $auditImagesValues[] = $auditId;
            $auditImagesValues[] = $itemId;
            $auditImagesValues[] = null; // BLOB placeholder

            $auditImages[] = $imageData;
            $auditImageCount++;
        }

        $itemId++;
    }

    if ($auditImageCount > 0) {
        $auditImageInsertSQL = "INSERT INTO closet_audit_images (auditId, itemId, image) VALUES";
        $auditImageInsertSQL .= implode(',', array_fill(0, $auditImageCount, " (?, ?, ?)"));

        $auditImageInsertStmt = $conn->prepare($auditImageInsertSQL);

        $auditImageTypes = str_repeat("iib", $auditImageCount);

        $auditImageInsertStmt->bind_param(
            $auditImageTypes,
            ...$auditImagesValues
        );

        foreach ($auditImages as $index => $imageData) {
            $auditImageInsertStmt->send_long_data(($index * 3) + 2, $imageData);
        }

        $auditImageInsertStmt->execute();
        $auditImageInsertStmt->close();
    }

    $auditItemsInsertSQL = "INSERT INTO closet_audit_items (auditId, itemId, itemDescription, itemValue) VALUES";
    $auditItemsInsertSQL .= implode(',', array_fill(0, $auditItemCount, " (?, ?, ?, ?)")) . ";";
    $auditItemsInsertStmt = $conn->prepare($auditItemsInsertSQL);
    $auditItemsInsertStmt->bind_param($auditItemsTypeString, ...$auditItemsValuesList);
    $auditItemsInsertStmt->execute();
    $auditItemsInsertStmt->close();

    if($auditCommentCount > 0) {
        $auditCommentsInsertSQL = "INSERT INTO closet_audit_comments (auditId, itemId, comment) VALUES";
        $auditCommentsInsertSQL .= implode(',', array_fill(0, $auditCommentCount, " (?, ?, ?)")) . ";";
        $auditCommentsInsertStmt = $conn->prepare($auditCommentsInsertSQL);
        $auditCommentsInsertStmt->bind_param($auditCommentsTypeString, ...$auditCommentsValuesList);
        $auditCommentsInsertStmt->execute();
        $auditCommentsInsertStmt->close();
    }

    $conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Closet Audit</title>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/x-icon" href="/imgs/favicon.png">
    <link rel="manifest" href="/manifest.json">
</head>
<body>
    <h2>Closet audit successfully submitted for<br><?= $buildingName ?>.</h2>
    <button class="big" onclick="window.location.href='/index.php'">Home</button>
</body>
<style>
    body {
        width: 100%;
        height: 100%;
        margin: auto;
        padding: 30px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    h2 {
        text-align: center;
    }
</style>
</html>
<?php } ?>