<?php
include 'login.php';
include 'accountProperties.php';

if (!accountProperties('Add Building')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to access this page.');
}

$errors = [];
$successMessage = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'db.php';

    $insId = bin2hex(random_bytes(8));
    $insName = $_POST['name'];
    $maxLength = 80;
    $insManager = $_POST['manager'];
    $insIc = $_POST['contractor'];
    $monday = isset($_POST['monday']) ? 1 : 0;
    $tuesday = isset($_POST['tuesday']) ? 1 : 0;
    $wednesday = isset($_POST['wednesday']) ? 1 : 0;
    $thursday = isset($_POST['thursday']) ? 1 : 0;
    $friday = isset($_POST['friday']) ? 1 : 0;
    $saturday = isset($_POST['saturday']) ? 1 : 0;
    $sunday = isset($_POST['sunday']) ? 1 : 0;

    // Validate contractor exists
    $check = $conn->prepare("SELECT COUNT(*) FROM contractors WHERE name = ?");
    $check->bind_param("s", $insIc);
    $check->execute();
    $check->bind_result($exists);
    $check->fetch();
    $check->close();

    if($insName === "") {
        $errors[] = "Building Name cannot be empty.";
    } else if(strlen($insName) > $maxLength) {
        $errors[] = "Building Name cannot exceed {$maxLength} characters.";
    } else if($insManager === "---" || $insManager === "") {
        $errors[] = "Manager cannot be empty.";
    } else if($insIc === "") {
        $errors[] = "Contractor cannot be empty.";
    } else if($exists == 0) {
        $errors[] = 'Contractor "' . $insIc . '" does not exist.';
    } else if(!$monday && !$tuesday && !$wednesday && !$thursday && !$friday && !$saturday && !$sunday) {
        $errors[] = "No days are selected.";
    }

    if(empty($errors)) {
        $ins = $conn->prepare("INSERT INTO buildings (`id`, `name`, `manager`, `ic`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        try {
            $ins->execute([$insId, $insName, $insManager, $insIc, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday, $sunday]);
            $successMessage = 'Insertion successful: "' . $insName . '" added';
            // Optionally clear POST so form resets after success
            $_POST = [];
        } catch (Exception $e) {
            if ($e->getCode() == 1062) {
                $errors[] = 'Insertion failed, "' . $insName . '" is already in building list.';
            } else {
                $errors[] = $e->getMessage();
            }
        }
    }

    $conn->close();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Add Building</title>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/x-icon" href="/imgs/favicon.png">
    <link rel="manifest" href="/manifest.json">
</head>
<body>

<header>
    <button onclick="window.location.href='../index.php'" class="big">&#8592 Back to Home</button>
    <h3>Add Building</h3>
    <div>
        <button onclick="window.location.href = '/index.php?logout=logout';" class="big">Logout</button>
        <div style="display: inline-block; vertical-align: middle; line-height: 90%;">
            <span style="font-size: 12px; margin: 0;">Logged in as:<br><?php echo htmlspecialchars($_SESSION['username'])?></span>
        </div>
    </div>
</header>
<main>
    <div class="contentContainer">
        <div class="card">
            <h2>Add New Building</h2>
            <?php if (!empty($errors)): ?>
                <div style="color: red; margin-bottom: 1em;">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($successMessage !== ''): ?>
                <div style="color: green; margin-bottom: 1em;">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <form action="addBuilding.php" method="post">
                <label for="Building Name">Building Name</label><br>
                <input type="text" id="Building Name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" style="width: 240px"><br><br>

                <label for="Manager">Manager</label><br>
                <select id="Manager" name="manager" style="width: 240px;"></select><br><br>

                <label for="Contractor">Contractor</label><br>
                <input type="text" id="Contractor" name="contractor" value="<?php echo isset($_POST['contractor']) ? htmlspecialchars($_POST['contractor']) : ''; ?>" style="width: 240px" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" placeholder="Search contractors...">
                <div id="contractorSuggestions" class="contractorSuggestions"></div><br><br>

                <div id="dayChecks" style="display: flex; gap: 10px; flex-direction: column;">
                    <div style="display: flex; gap: 16px;">
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            M
                            <input id="monday" type="checkbox" name="monday" <?php echo isset($_POST['monday']) ? 'checked' : ''; ?> onclick="handleDayCheck(this.checked)">
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            T
                            <input id="tuesday" type="checkbox" name="tuesday" <?php echo isset($_POST['tuesday']) ? 'checked' : ''; ?> onclick="handleDayCheck(this.checked)">
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            W
                            <input id="wednesday" type="checkbox" name="wednesday" <?php echo isset($_POST['wednesday']) ? 'checked' : ''; ?> onclick="handleDayCheck(this.checked)">
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            Th
                            <input id="thursday" type="checkbox" name="thursday" <?php echo isset($_POST['thursday']) ? 'checked' : ''; ?> onclick="handleDayCheck(this.checked)">
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            F
                            <input id="friday" type="checkbox" name="friday" <?php echo isset($_POST['friday']) ? 'checked' : ''; ?> onclick="handleDayCheck(this.checked)">
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            Sat
                            <input id="saturday" type="checkbox" name="saturday" <?php echo isset($_POST['saturday']) ? 'checked' : ''; ?> onclick="handleDayCheck(this.checked)">
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            Sun
                            <input id="sunday" type="checkbox" name="sunday" <?php echo isset($_POST['sunday']) ? 'checked' : ''; ?> onclick="handleDayCheck(this.checked)">
                        </label>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            Check MWF
                            <input id="MWFCheckbox" type="checkbox" name="mwf" <?php echo isset($_POST['mwf']) ? 'checked' : ''; ?> onclick="handleDayCheckMWF(this.checked)">
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            Check M-F
                            <input id="MFCheckbox" type="checkbox" name="mf" <?php echo isset($_POST['mf']) ? 'checked' : ''; ?> onclick="handleDayCheckMF(this.checked)">
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center;">
                            Check All
                            <input id="allDaysCheckbox" type="checkbox" name="alldays" <?php echo isset($_POST['monday']) ? 'alldays' : ''; ?> onclick="handleDayCheckAll(this.checked)">
                        </label>
                    </div>

                </div><br>

                <input type="submit">
            </form>
        </div>
    </div>
</main>

<script src="/js/handleDayChecks.js"></script>
<script src="/js/loadElementVariables.js"></script>
<script src="/js/encodeHTML.js"></script>
<script src="/js/searchContractors.js"></script>
<script>
    searchContractors('Contractor', 'contractorSuggestions');
</script>
<script src="/js/fillSelectMenu.js"></script>
<script>
    accessKeyReady.then(() => {
        fetch('/php/searchManagers.php?key=' + accessKey)
            .then(response1 => response1.json())
            .then(managerData => {
                fetch('/php/searchContractors.php?key=' + accessKey)
                    .then(response2 => response2.json())
                    .then(icData => {
                        contractorList = icData;
                        fillSelectMenu("Manager", managerData, "<?php echo $_POST['manager'] ?? '---'; ?>");
                    });
            });
    });
</script>
<script src="/js/adjustMainMargin.js"></script>

</body>
</html>