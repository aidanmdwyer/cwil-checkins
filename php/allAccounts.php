<?php
include 'login.php';
include 'accountProperties.php';

if (!accountProperties('Accounts Page')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to access this page.');
}

// Connect to database
require_once 'db.php';

if(isset($_POST['username'])) {
    $insUsername = $_POST['username'];
    $accountType = "admin";
    $emptyPassword = "";
    $maxLength = 80;

    if($insUsername === "") {
        $insertError = 'Username cannot be empty.';
    } else if(strtolower($insUsername) === "developer" || strtolower($insUsername) === "admin" || strtolower($insUsername) === "manager" || strtolower($insUsername) === "contractor" || strtolower($insUsername) === "default") {
        $insertError = 'Username forbidden.';
    } else if(strlen($insUsername) > $maxLength) {
        $insertError = 'Username cannot exceed ' . $maxLength . ' characters.';
    } else {
        $insert = $conn->prepare("INSERT INTO users (username, passwordHash, accountType) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $insUsername, $emptyPassword, $accountType);
        try {
            $insert->execute();
            $insertSuccess = 'Insertion successful: "' . htmlspecialchars($insUsername) . '" added to account list';
        } catch (Exception $e) {
            if ($e->getCode() == 1062) {
                $insertError = 'Insertion failed, "' . htmlspecialchars($insUsername) . '" is already in account list.';
            } else {
                $insertError = $e->getMessage();
            }
        }
    }
}

// Fetch all users
$usersResult = $conn->query("
    SELECT username, accountType, passwordHash, resetTimer, 
        EXISTS (
            SELECT 1 FROM account_properties ap WHERE ap.accountName = u.username
        ) AS permissionsChanged
        FROM users u
        ORDER BY accountType, username
");

$usersByType = [
    'developer'  => [],
    'admin'      => [],
    'manager'    => [],
    'contractor' => [],
];

while ($row = $usersResult->fetch_assoc()) {
    $type = strtolower($row['accountType']);
    if (isset($usersByType[$type])) {
        $usersByType[$type][] = $row;
    }
}

// Find max number of users in a column to align table rows
$maxRows = max(array_map('count', $usersByType));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>All Accounts</title>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/x-icon" href="/imgs/favicon.png">
    <link rel="manifest" href="/manifest.json">
</head>
<body>

<script src="/js/loadElementVariables.js"></script>
<script src="/js/encodeHTML.js"></script>
<script src="/js/adjustMainMargin.js"></script>
<script src="/js/copyAccountLink.js"></script>

<header>
    <button onclick="window.location.href='../index.php'" class="big"><span style="font-size: 20px;">&#8592</span> Back to Home</button>
    <h3>All Accounts</h3>
    <div>
        <button onclick="window.location.href = '/index.php?logout=logout';" class="big">Logout</button>
        <div style="display: inline-block; vertical-align: middle; line-height: 90%;">
            <span style="font-size: 12px; margin: 0;">Logged in as:<br><?php echo htmlspecialchars($_SESSION['username'])?></span>
        </div>
    </div>
</header>
<main>
    <div class="contentContainer">
        <div style="display: flex; flex-direction: row;">
            <div style="display: flex; flex-direction: column;">
                <div class="card">
                    <h2>Create New Admin Account</h2>
                    <p>Enter the username and then have the user reset the password (they cannot log in until the password is set).</p>
                    <form method="POST" action="allAccounts.php">
                        <label>
                            Username:
                            <span style="display: flex; flex-direction: row; gap: 5px;">
                                <input id="username" type="text" name="username" style="width: 240px;">
                                <input type="submit">
                            </span>
                        </label>
                    </form>
                    <?php
                    if(isset($insertSuccess)) {
                        echo '<br><div style="color: green;">' . $insertSuccess . '</div>';
                    } else if (isset($insertError)) {
                        echo '<br><div style="color: red;">' . $insertError . '</div>';
                    }
                    ?>
                </div>
                <div class="card">
                    <h2>All Accounts</h2>
                    <?php
                    if (isset($_GET['deleted'])) {
                        echo '<div style="color: green;">Successfully deleted "' . $_GET['deleted'] . '"</div><br>';
                    }
                    ?>

                        <?php

                        foreach (['admin', 'manager', 'contractor'] as $accountType) {
                            echo '<table style="width: 650px; border-collapse: collapse; margin-bottom: 15px;">
                        <tbody>';
                            $accountTypeArg = '"' . $accountType . '"';

                            // Type header row
                            echo "<tr>";
                            echo "<th style='text-align: left; padding: 20px; text-transform: capitalize;'>" . htmlspecialchars($accountType) . "</th>";
                            echo "<th style='text-align: center; width:30px; font-size: 12px;'>Account Link</th>";
                            echo "<th style='text-align: center; width:30px; font-size: 10px;'>Password Reset Link</th>";
                            echo "<th style='text-align: center; width:80px; font-size: 12px;'>Status</th>";
                            echo "<th style='text-align: center; width:80px; font-size: 10px;'>Edit Permissions</th>";
                            echo "<th style='text-align: center; width:30px; font-size: 12px;'>Delete</th>";
                            echo "</tr>";

                            $switch = false;

                            if (!empty($usersByType[$accountType])) {
                                foreach ($usersByType[$accountType] as $userRow) {
                                    $bgColor = $switch ? '#F3F3F3' : '#E5E5E5';
                                    $usernameRaw = $userRow['username'];
                                    $usernameEsc = htmlspecialchars($usernameRaw);
                                    $usernameEncoded = rawurlencode($usernameRaw);

                                    // --- compute status ---
                                    $statusText  = '';
                                    $statusColor = '';

                                    if (!empty($userRow['resetTimer'])) {
                                        $resetTs   = strtotime($userRow['resetTimer']);
                                        $deadline  = $resetTs + 23 * 3600;
                                        $remaining = $deadline - time();

                                        if ($remaining > 3600) {
                                            $hoursLeft = (int)ceil($remaining / 3600);
                                            $statusText = $hoursLeft . 'h until link expires';
                                            $statusColor = 'green';
                                        } else if ($remaining > 0) {
                                            $minutesLeft = (int)ceil($remaining / 60);
                                            $statusText = $minutesLeft . 'm until link expires';
                                            $statusColor = 'green';
                                        }
                                    } else if ($userRow['passwordHash'] === '' || is_null($userRow['passwordHash'])) {
                                        $statusText  = 'Password not set';
                                        $statusColor = 'red';
                                    }

                                    echo "<tr>";
                                    // username
                                    echo "<td style='padding: 6px 12px; background-color: {$bgColor};'>" . $usernameEsc . "</td>";

                                    // account link button
                                    echo "<td style='text-align: center; background-color: {$bgColor};'>
                                <button style='border: none; background-color: transparent' 
                                        onclick='copyLoginLink(" . json_encode($usernameEncoded) . ", " . $accountTypeArg . ", this)'>&#128203</button>
                              </td>";

                                    // reset link button
                                    echo "<td style='text-align: center; background-color: {$bgColor};'>
                                <button style='border: none; background-color: transparent' 
                                        onclick='copyResetLink(" . json_encode($usernameEncoded) . ", this)'>&#128260</button>
                              </td>";

                                    // status cell
                                    $styleColor = $statusColor ? "color: {$statusColor};" : '';
                                    echo "<td style='text-align: center; background-color: {$bgColor}; font-size: 12px; {$styleColor}'>"
                                        . htmlspecialchars($statusText)
                                        . "</td>";

                                    // edit permissions cell
                                    echo "<td style='text-align: center; background-color: {$bgColor};'>
                                <button style='border: none; background-color: transparent' 
                                        onclick='(() => {window.location.href = `/php/allAccounts.php?accountType=` + `$accountType` + `&accountName=` + `$usernameEncoded`})()'>&#128394 " . ($userRow['permissionsChanged'] === "1" ? "<span style='color: green; font-weight: bold;'>Custom</span>" : "<span style='opacity: 0.5;'>Default</span>") . "</button>
                              </td>";

                                    // delete form cell
                                    echo "<td style='text-align: center; background-color: {$bgColor};'>
                                <form method='POST' action='deleteAccount.php' style='display:inline;' data-name='" . $usernameEsc . "'>
                                    <input type='hidden' name='deleteName' value='" . $usernameEsc . "'>
                                    <button type='submit' style='border: none; background-color: transparent' onclick='return confirmDelete(this)'>&#128465</button>
                                </form>
                              </td>";

                                    echo "</tr>";

                                    $switch = !$switch;
                                }
                            } else {
                                echo "<tr>
                            <td style='padding: 6px 12px; background-color: #E5E5E5'>No users</td>
                            <td style='background-color: #E5E5E5'></td>
                            <td style='background-color: #E5E5E5'></td>
                            <td style='background-color: #E5E5E5'></td>
                            <td style='background-color: #E5E5E5'></td>
                            <td style='background-color: #E5E5E5'></td>
                          </tr>";
                            }
                        }

                        echo '</tbody>
                    </table>';
                        ?>
                </div>
            </div>
            <div style="display: flex; flex-direction: column;">
                <div class="card" style="min-width: 400px;">

                    <h2>Account Permissions</h2>

                    <p>Select an account type. You may either edit the default permissions for that account type, or the permissions of an individual account.</p>

                    <form method="GET" action="allAccounts.php">
                        <div style="display: flex; flex-direction: row; gap: 5px;">
                            <label>
                                <select name="accountType" id="accountTypeSelect" onchange="(() => {document.getElementById('accountNameSelect').selectedIndex = 0; this.form.submit()})()">
                                    <option value="---">---</option>
                                    <option value="admin" <?php echo rawurldecode($_GET['accountType']) === 'admin' ? 'selected' : ''?>>Admin</option>
                                    <option value="manager" <?php echo rawurldecode($_GET['accountType']) === 'manager' ? 'selected' : ''?>>Manager</option>
                                    <option value="contractor" <?php echo rawurldecode($_GET['accountType']) === 'contractor' ? 'selected' : ''?>>Contractor</option>
                                </select>
                            </label>
                            <label style="<?php echo (!isset($_GET['accountType']) || rawurldecode($_GET['accountType']) === '---') ? 'display: none;' : ''?>">
                                <select name="accountName" id="accountNameSelect" onchange="this.form.submit()">
                                    <option value="default" <?php echo rawurldecode($_GET['accountName']) === 'default' ? 'selected' : ''?>>Default Permissions</option>
                                    <?php
                                    if(isset($_GET['accountType'])) {
                                        foreach ($usersByType[$_GET['accountType']] as $userRow) {
                                            $usernameRaw = $userRow['username'];
                                            echo '<option value="' . rawurlencode($usernameRaw) . '"' . (rawurldecode($_GET['accountName']) === $usernameRaw ? 'selected' : '') . '>' . htmlspecialchars($usernameRaw) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </label>
                        </div>
                    </form>

                    <br>

                    <form method="POST"
                          action="editAccountProperties.php"
                          onsubmit="return confirm('<?php echo
                              (rawurldecode($_GET['accountName']) === 'default') ?
                                  'Are you sure you want to edit the default permissions for ' . rawurldecode($_GET['accountType']) . ' accounts?' :
                                  'Are you sure you want to edit the account permissions of ' . rawurldecode($_GET['accountName']) . '?'; ?>')"
                          style="display: flex; flex-direction: column; justify-content: flex-start; <?php echo (!$_GET['accountType'] || $_GET['accountType'] === '---') ? 'display: none;' : ''?>"
                    >

                        <?php
                        if (isset($_GET['editPropertiesSuccess'])) {
                            echo '<p style="color: green; margin-top: 0;">' . htmlspecialchars($_GET['editPropertiesSuccess']) . '</p>';
                        } else if (isset($_GET['editPropertiesError'])) {
                            echo '<p style="color: red; margin-top: 0;">' . htmlspecialchars($_GET['editPropertiesError']) . '</p>';
                        }
                        ?>

                        <input type="hidden" name="resetToDefaults" id="resetToDefaultsInput" value="false">
                        <input type="hidden" name="accountType" value="<?php echo rawurldecode($_GET['accountType'])?>">
                        <input type="hidden" name="accountName" value="<?php echo rawurldecode($_GET['accountName'])?>">
                                <?php
                                if(isset($_GET['accountName']) && isset($_GET['accountType']) && $_GET['accountType'] !== '---') {
                                    $propertiesStmt = $conn->prepare("SELECT property, permission FROM account_properties WHERE accountName = ?");
                                    $insName = rawurldecode($_GET['accountName']) === 'default' ? rawurldecode($_GET['accountType']) : rawurldecode($_GET['accountName']);
                                    $propertiesStmt->bind_param("s", $insName);
                                    $propertiesStmt->execute();
                                    $propertiesResult = $propertiesStmt->get_result();
                                    $properties = [];

                                    if($propertiesResult->num_rows > 0) { //if the account has properties defined
                                        while ($row = $propertiesResult->fetch_assoc()) {
                                            $properties[$row['property']] = $row['permission'];
                                        }
                                    } else { //else use default properties
                                        $defaultPropertiesStmt = $conn->prepare("SELECT property, permission FROM account_properties WHERE accountName = ?");
                                        $accountType = rawurldecode($_GET['accountType']);
                                        $defaultPropertiesStmt->bind_param("s", $accountType);
                                        $defaultPropertiesStmt->execute();
                                        $defaultPropertiesResult = $defaultPropertiesStmt->get_result();

                                        while ($row = $defaultPropertiesResult->fetch_assoc()) {
                                            $properties[$row['property']] = $row['permission'];
                                        }
                                    }

                                    //[property name, whether it can be changed]
                                    $propertyData = [
                                        "Page Access" => [
                                            ["Home Page", false],
                                            ["Archives Page", true],
                                            ["Add Building Page", true],
                                            ["Import Page", true],
                                            ["Managers Page", true],
                                            ["Accounts Page", !(($_GET['accountName'] === 'default') || ($_GET['accountName'] === $_SESSION['username']))],
                                            ["Contractors Page", true],
                                        ],
                                        "Data Access" => [
                                            ["Select/Edit Multiple Buildings", true],
                                            ["Toggle Check-ins", true],
                                            ["See Building Name", true],
                                            ["See Days", true],
                                            ["See Manager", true],
                                            ["Print QR", true],
                                            ["See IC", true],
                                            ["Edit Buildings", true],
                                            ["See Check-in Status", true],
                                            ["Access Inactive Buildings", true],
                                            ["See Check-in Time", true],
                                            ["Export Buildings", true],
                                        ],
                                        "Filter Options" => [
                                            ["Search Building Name", true],
                                            ["Filter IC", true],
                                            ["Filter Manager", true],
                                            ["Filter Today Only", true],
                                        ],
                                    ];

                                    foreach ($propertyData as $section => $list) {

                                        echo "
                            <table style='background-color: white; border-collapse: collapse; border: 1px solid black; margin-bottom: 15px;'>
                                <tbody>
                                    <tr style='background: transparent; border: none;'>
                                        <th style='background: transparent; border: none; text-align: center; padding: 10px; text-decoration: underline;' colspan='2'>$section</th>
                                    </tr>";

                                        $switch = false;
                                        foreach ($list as $value) {
                                            if(!$switch) {
                                                echo "<tr style='background: transparent; border: none;'>";
                                            }
                                            echo "
                                            <td style='background: transparent; border: none; padding: 2px 10px 6px 10px;'>
                                                <input type='hidden' name='properties[" . rawurlencode($value[0]) . "]' value='0'>
                                                <label" . ($value[1] ? "" : " style='opacity: 0.5;'") . ">
                                                <input type='checkbox' name='properties[" . rawurlencode($value[0]) . "]' value='1'" . ($value[1] ? "" : " onclick='return false;'") .
                                                ($properties[$value[0]] === 1 ? ' checked' : '') .
                                                "> " . htmlspecialchars($value[0]) . "</label></td>";
                                            if($switch) {
                                                echo "</tr>";
                                            }
                                            $switch = !$switch;
                                        }
                                        if($switch) {
                                            echo "<td style='background: transparent; border: none;'></td></tr>";
                                        }
                                        echo "
                                            </tbody>
                                        </table>
                                        ";
                                    }
                                }
                                ?>

                        <br>
                        <div style="display: flex; flex-direction: row; gap: 15px;">
                            <button type="submit" class="big">Submit</button>
                        <?php
                        if(rawurldecode($_GET['accountName']) !== 'default') { //don't show reset to defaults button for default permissions
                            ?>
                            <button type="button" class="big" onclick="(() => {
                                    if(confirm('Are you sure you want to reset the account permissions of <?php echo rawurldecode($_GET['accountName']); ?> to the <?php echo rawurldecode($_GET['accountType']); ?> defaults?')) {
                                    document.getElementById('resetToDefaultsInput').value = 'true';
                                    this.form.submit();
                                    }
                                    })()">Reset to Defaults</button>
                            <br>
                        <?php } ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>

<?php
$conn->close();
?>