<?php
include 'login.php';
include 'accountProperties.php';

if (!accountProperties('Closet Audit')) {
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
    $date = $now->format("Y-m-d");

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

    $lastDateStmt = $conn->prepare("SELECT date FROM closet_audits WHERE building_name = ? ORDER BY date DESC LIMIT 1");
    $lastDateStmt->bind_param("s", $buildingName);
    $lastDateStmt->execute();
    $lastDateResults = $lastDateStmt->get_result();
    $lastAuditDate = $lastDateResults->fetch_assoc()['ic'];
    $lastDateStmt->close();
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

    <form method="POST">
        <input type="hidden" name="buildingName" value="<?=$buildingName?>">

        <h3>Date: <?=$date?></h3>
        <input type="hidden" name="date" value="<?=$date?>">
        <br>

        <?php if($lastAuditDate) { ?>
            <h3>Last Audit: <?=$lastAuditDate?></h3>
            <br>
        <?php } ?>

        <h3>Auditor: <?=$auditor?></h3>
        <input type="hidden" name="auditor" value="<?=$auditor?>">
        <br>
        
        <h3>Contractor: <?=$ic?></h3>
        <input type="hidden" name="ic" value="<?=$ic?>">
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
                "Spray bottles labeled with contents"
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
            foreach ($list as $item) {
                echo "
                <label>
                    <input type='hidden' name='auditItems[$item]' value='0'>
                    <input type='checkbox' name='auditItems[$item]'>
                    $item
                </label><br>";
            }
            echo "<br><div class='hr'></div>";
        }
        ?>

        <input type="submit">

    </form>
</body>
<style>
    html {
        width: 100%;
        height: 100%;
    }
    body {
        width: min(100%, 800px);
        margin: 0 auto;

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

</style>
</html>

<?php
} else { //after submission
    $buildingName = $_POST['buildingName'];
    $auditItems = $_POST['auditItems'];
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
    <?= $buildingName?>
    <br>
    <?php 
    foreach ($auditItems as $item => $value) {
        $itemValue1 = $value === true;
        $itemValue2 = $value === 1;
        $itemValue3 = $value === 'on';
        echo $item . ", " . $itemValue1 . ", " . $itemValue2 . ", " . $itemValue3 . "<br>";
    }
    ?>
</body>
</html>
<?php } ?>