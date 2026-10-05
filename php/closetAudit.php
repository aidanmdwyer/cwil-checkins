<?php
include 'login.php';
include 'accountProperties.php';

if (!accountProperties('Closet Audit')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to access this page.');
}

$buildingName = $_GET['buildingName'];
if(!$buildingName) {
    http_response_code(404);
    die('Page load failed.');
}

$auditor = $_SESSION['username'];
$now = new DateTime('now', new DateTimeZone('America/Chicago'));
$date = $now->format("Y-m-d");

// require_once 'db.php';

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

    <form>
        <h3>Date: <?=$date?></h3>
        <input type="hidden" name="date" value="<?=$date?>">
        <br>

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
                    <input type='checkbox' name='$item'>
                    $item
                </label><br>";
            }
            echo "<br><div class='hr'></div>";
        }
        ?>

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
        margin: 5px 0;
        padding: 0;
    }

</style>
</html>