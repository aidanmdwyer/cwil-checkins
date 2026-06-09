<?php
include 'login.php';
include 'accountProperties.php';

if (!accountProperties('Contractors Page')) {
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
    <title>Add Contractor</title>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/x-icon" href="/imgs/favicon.png">
    <link rel="manifest" href="/manifest.json">
    <script src="/modules/xlsx.full.min.js"></script>
</head>
<body>

<script src="/js/loadElementVariables.js"></script>
<script src="/js/encodeHTML.js"></script>
<script src="/js/copyAccountLink.js"></script>
<script src="/js/adjustMainMargin.js"></script>

<header>
    <button onclick="window.location.href='../index.php'" class="big"><span style="font-size: 20px;">&#8592</span> Back to Home</button>
    <h3>Contractors</h3>
    <div>
        <button onclick="window.location.href = '/index.php?logout=logout';" class="big">Logout</button>
        <div style="display: inline-block; vertical-align: middle; line-height: 90%;">
            <span style="font-size: 12px; margin: 0;">Logged in as:<br><?php echo htmlspecialchars($_SESSION['username'])?></span>
        </div>
    </div>
</header>
<?php
require_once 'db.php';

//submit new contractor
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $insName = $_POST['name'];
    $maxLength = 80;

    if($insName === "") {
        $insertError = 'Contractor Name cannot be empty.';
    } else if(strlen($insName) > $maxLength) {
        $insertError = 'Contractor Name cannot exceed ' . $maxLength . ' characters.';
    } else {
        $ins = $conn->prepare("INSERT INTO contractors (`name`) VALUES (?)");
        try {
            $ins->execute([$insName]);
            $insertSuccess = 'Insertion successful: "' . htmlspecialchars($insName) . '" added to contractor list';
        } catch(Exception $e) {
            if($e->getCode() == 1062) {
                $insertError = 'Insertion failed, "' . htmlspecialchars($insName) . '" is already in contractor list.';
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
            <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center;">
                <h2>All Contractors</h2>
                <button id="contractorExportButton" class="big" onclick="exportContractorSheet('contractorsTable', new Date().toISOString().slice(0,10) + '_contractors.xlsx')" style="margin-left: 30px;">Export</button>
            </div>
            <form id="searchContractorsForm">
                <label style="display: flex; flex-direction: column;">
                    Search
                    <span style="display: flex; flex-direction: row; gap: 5px; align-items: center;">
                        <input type="text" id="searchContractors" name="searchContractors"/>
                        <button type="submit" class="big">Enter</button>
                    </span>
                </label>
            </form>
            <br>
            <?php
            if($_SERVER['REQUEST_METHOD'] != 'POST') {
                // Display deletion success message, if any
                if (isset($_GET['deleted'])) {
                    $deletedName = $_GET['deleted'];
                    echo '<div style="color: green;">Successfully deleted "' . htmlspecialchars($deletedName) . '"</div><br>';
                }
                // Display error messages, if any
                if (isset($_GET['error'])) {
                    echo '<div style="color: red;">Error: ' . $_GET['error'] . '</div><br>';
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

            //display table
            $query = "SELECT * FROM `contractors`";
            $result = $conn->query($query);

            echo "<table id='contractorsTable' style='width: 650px'>";
            echo "<thead><tr><th>Contractor Name</th><th style='text-align: center; width:30px; font-size: 12px;'>Account Link</th><th style='text-align: center; width:30px; font-size: 10px;'>Password Reset Link</th><th style='text-align: center; width:80px; font-size: 12px;'>Status</th><th style='text-align: center; width:30px; font-size: 12px;'>Delete</th></tr></thead>";
            echo "<tbody>";

            $switch = false;
            while($row = $result->fetch_assoc()) {
                echo "<tr class='" . ($switch ? 'odd' : 'even') . "'>";

                echo "<td>" . htmlspecialchars($row['name']) . "</td>";

                $accountType = '"contractor"';
                echo "<td style='text-align: center;'>
            <button style='border: none; background-color: transparent' onclick='copyAccountLink(" . json_encode(htmlspecialchars($row['name'])) . ", " . $accountType . ", this)'>&#128203</button></td>";

                $accountMade = in_array($row['name'], $usernames, true);
                echo "<td style='text-align: center;'>";
                if ($accountMade) {
                    echo "<button style='border: none; background-color: transparent' onclick='copyResetLink(" . json_encode(htmlspecialchars($row['name'])) . ", this)'>&#128260</button>";
                }
                echo "</td>";

                // --- Status cell (mimics allAccounts.php; 'account not made' when no user) ---

                $statusText = '';
                $statusColor = '';
                if ($accountMade) {
                    $rt = $userReset[$row['name']] ?? null;
                    if (!empty($rt)) {
                        $resetTs = strtotime($rt);
                        $deadline = $resetTs + 23 * 3600;
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
                echo "<td style='text-align: center;; font-size: 12px; {$styleColor}'>" . htmlspecialchars($statusText) . "</td>";

                echo "<td style='text-align: center;'>";
                echo '
                <form method="POST" action="deleteContractor.php" style="display:inline;" data-name="' . htmlspecialchars($row['name']) . '">
                    <input type="hidden" name="contractor_name" value="' . htmlspecialchars($row['name']) . '">
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
            <h2>Add New Contractor</h2>
            <form action="addContractor.php" method="post">
                <label for="Contractor Name">Contractor Name</label><br>
                <input type="text" id="Contractor Name" placeholder="Bob's Cleaning LLC" name="name" value="<?php echo (isset($_POST['name']) && isset($insertError)) ? htmlspecialchars($_POST['name']) : ''; ?>" style="width: 370px">
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
<script src="/js/tableToExcel.js"></script>
<script>
    document.getElementById('searchContractorsForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const searchTerm = document.getElementById('searchContractors').value.toLowerCase();
        const tbody = document.getElementById('contractorsTable').getElementsByTagName('tbody')[0];
        const trs = Array.from(tbody.children);

        // filter rows
        trs.forEach(tr => {
            tr.style.display = tr.children[0].textContent.toLowerCase().includes(searchTerm)
                ? 'table-row'
                : 'none';
        });

        // reapply alternating colors
        const visibleRows = trs.filter(tr => tr.style.display !== 'none');
        visibleRows.forEach((tr, i) => {
            tr.classList.remove('even', 'odd');
            tr.classList.add(i % 2 === 0 ? 'even' : 'odd');
        });
    });
</script>
<style>
    tbody tr.even td {
        background-color: #E5E5E5;
    }
    tbody tr.odd td {
        background-color: #F3F3F3;
    }
</style>
</html>
