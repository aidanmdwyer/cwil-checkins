<?php
include 'login.php';
include 'accountProperties.php';

if (!accountProperties('Managers Page')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to access this page.');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Add Manager</title>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/x-icon" href="/imgs/favicon.png">
    <link rel="manifest" href="/manifest.json">
</head>
<body>

<script src="/js/loadElementVariables.js"></script>
<script src="/js/encodeHTML.js"></script>
<script src="/js/copyAccountLink.js"></script>
<script src="/js/adjustMainMargin.js"></script>

<header>
    <button onclick="window.location.href='../index.php'" class="big"><span style="font-size: 20px;">&#8592</span> Back to Home</button>
    <h3>Managers</h3>
    <div>
        <button onclick="window.location.href = '/index.php?logout=logout';" class="big">Logout</button>
        <div style="display: inline-block; vertical-align: middle; line-height: 90%;">
            <span style="font-size: 12px; margin: 0;">Logged in as:<br><?php echo htmlspecialchars($_SESSION['username'])?></span>
        </div>
    </div>
</header>
<?php
require_once 'db.php';

//submit new manager
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $insName = $_POST['name'];
    $maxLength = 40;

    if($insName === "") {
        $insertError = 'Manager Name cannot be empty.';
    } else if(strlen($insName) > $maxLength) {
        $insertError = 'Manager Name cannot exceed ' . $maxLength . ' characters.';
    } else {
        $ins = $conn->prepare("INSERT INTO managers (`name`) VALUES (?)");
        try {
            $ins->execute([$insName]);
            $insertSuccess = 'Insertion successful: "' . htmlspecialchars($insName) . '" added to manager list';
        } catch (Exception $e) {
            if ($e->getCode() == 1062) {
                $insertError = 'Insertion failed, "' . htmlspecialchars($insName) . '" is already in manager list.';
            } else {
                $insertError = $e->getMessage();
            }
        }
    }
}
?>
<main>
    <div class="contentContainer">

        <div class="card">
            <h2>All Managers</h2>
            <?php
            if($_SERVER['REQUEST_METHOD'] != 'POST') {
                // Display deletion success message, if any
                if (isset($_GET['deleted'])) {
                    $deletedName = $_GET['deleted'];
                    echo '<span style="color: green;">Successfully deleted "' . htmlspecialchars($deletedName) . '"</span><br>';
                }
                // Display error messages, if any
                if (isset($_GET['error'])) {
                    echo '<span style="color: red;">Error: ' . $_GET['error'] . '</span><br>';
                }
            }

            // Build username list (HTML-escaped) and map to resetTimer
            $usernames = [];
            $userReset = [];
            $userResult = $conn->query("SELECT username, resetTimer FROM users");
            while ($userRow = $userResult->fetch_assoc()) {
                $usernames[] = $userRow['username'];
                $userReset[$userRow['username']] = $userRow['resetTimer'];
            }

            //display table with Status column
            $query = "SELECT * FROM `managers`";
            $result = $conn->query($query);

            echo "<table style='width: 450px'>";
            echo "<thead><tr><th style='width:450px'>Manager Name</th><th style='text-align: center; width:30px; font-size: 12px;'>Account Link</th><th style='text-align: center; width:30px; font-size: 10px;'>Password Reset Link</th><th style='text-align: center; width:80px; font-size: 12px;'>Status</th><th style='text-align: center; width:30px; font-size: 12px;'>Delete</th></tr></thead>";
            echo "<tbody>";

            $switch = FALSE;
            while($row = $result->fetch_assoc()) {
                $bgColor = $switch ? '#F3F3F3' : '#E5E5E5';
                echo "<tr>";

                echo "<td style='background-color: {$bgColor}'>" . htmlspecialchars($row['name']) . "</td>";

                $accountType = '"manager"';
                echo "<td style='text-align: center; background-color: {$bgColor}'>
                <button style='border: none; background-color: transparent' onclick='copyAccountLink(" . json_encode(htmlspecialchars($row['name'])) . ", " . $accountType . ", this)'>&#128203</button></td>";

                $accountMade = in_array($row['name'], $usernames, true);
                echo "<td style='text-align: center; background-color: {$bgColor}'>";
                if($accountMade) {
                    echo "<button style='border: none; background-color: transparent' onclick='copyResetLink(" . json_encode(htmlspecialchars($row['name'])) . ", this)'>&#128260</button>";
                }
                echo "</td>";

                $statusText = '';
                $statusColor = '';
                if ($accountMade) {
                    $rt = $userReset[$row['name']] ?? null;
                    if (!empty($rt)) {
                        $resetTs   = strtotime($rt);
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
                    }
                } else {
                    $statusText = 'Account not made';
                    $statusColor = 'red';
                }
                $styleColor = $statusColor ? "color: {$statusColor};" : '';
                echo "<td style='text-align: center; background-color: {$bgColor}; font-size: 12px; {$styleColor}'>" . htmlspecialchars($statusText) . "</td>";

                echo "<td style='text-align: center; background-color: {$bgColor}'>";
                echo '
                    <form method="POST" action="deleteManager.php" style="display:inline;" data-name="' . htmlspecialchars($row['name']) . '">
                        <input type="hidden" name="manager_name" value="' . htmlspecialchars($row['name']) . '">
                        <button type="submit" style="border: none; background-color: transparent" onclick="return confirmDelete(this)">&#128465</button>
                    </form>';
                echo "</td>";

                echo "</tr>";
                $switch = !$switch;
            }

            echo "</tbody>";
            echo "</table>";

            $conn->close();

            ?>
        </div>
        <div class="card">
            <h2>Add New Manager</h2>
            <form action="addManager.php" method="post">
                <label for="Manager Name">Manager Name</label><br>
                <input type="text" id="Manager Name" placeholder="John Doe" name="name" value="<?php echo (isset($_POST['name']) && isset($insertError)) ? htmlspecialchars($_POST['name']) : ''; ?>" style="width: 240px">
                <input type="submit">
            </form>
            <?php
            if(isset($insertSuccess)) {
                echo '<br><div style="color: green;">' . $insertSuccess . '</div>';
            } else if(isset($insertError)) {
                echo '<br><div style="color: red;">' . $insertError . '</div>';
            }
            ?>
        </div>
    </div>
</main>
</body>
</html>
