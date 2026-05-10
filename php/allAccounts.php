<?php
include 'login.php';
include 'accountProperties.php';

if (!accountProperties('All Accounts')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to access this page.');
}

// Connect to database
require_once 'db.php';

if(isset($_POST['username'])) {
    $username = $_POST['username'];
    $accountType = "admin";
    $emptyPassword = "";
    $maxLength = 80;

    if($username === "") {
        $insertError = 'Username cannot be empty.';
    } else if(strlen($username) > $maxLength) {
        $insertError = 'Username cannot exceed ' . $maxLength . ' characters.';
    } else {
        $insert = $conn->prepare("INSERT INTO users (username, passwordHash, accountType) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $username, $emptyPassword, $accountType);
        try {
            $insert->execute();
            $insertSuccess = 'Insertion successful: "' . htmlspecialchars($username) . '" added to account list';
        } catch (Exception $e) {
            if ($e->getCode() == 1062) {
                $insertError = 'Insertion failed, "' . htmlspecialchars($username) . '" is already in account list.';
            } else {
                $insertError = $e->getMessage();
            }
        }
    }
}

// Fetch all users
$result = $conn->query("
    SELECT username, accountType, passwordHash, resetTimer
    FROM users
    ORDER BY accountType, username
");

$usersByType = [
    'developer'  => [],
    'admin'      => [],
    'manager'    => [],
    'contractor' => [],
];

while ($row = $result->fetch_assoc()) {
    $type = strtolower($row['accountType']);
    if (isset($usersByType[$type])) {
        // keep raw row (we'll escape on output)
        $usersByType[$type][] = $row;
    }
}

// Find max number of users in a column to align table rows
$maxRows = max(array_map('count', $usersByType));

$conn->close();
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
        <div class="card">
            <h2>Create New Admin Account</h2>
            <p>Enter the username and then have the user reset the password (they cannot login until the password is set).</p>
            <form method="POST" action="allAccounts.php">
                <label for="username">Username:</label><br>
                <input id="username" type="text" name="username" style="width: 240px;">
                <input type="submit">
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
            <table style="width: 650px; border-collapse: collapse;">
                <tbody>
                <?php

                foreach (['admin', 'manager', 'contractor'] as $accountType) {

                    $accountTypeArg = '"' . $accountType . '"';

                    // Type header row
                    echo "<tr style='background-color: #ccc;'>";
                    echo "<th style='text-align: left; padding: 25px; text-transform: uppercase;'>" . htmlspecialchars($accountType) . "</th>";
                    echo "<th style='text-align: center; width:30px; font-size: 12px;'>Account Link</th>";
                    echo "<th style='text-align: center; width:30px; font-size: 10px;'>Password Reset Link</th>";
                    echo "<th style='text-align: center; width:80px; font-size: 12px;'>Status</th>";
                    echo "<th style='text-align: center; width:30px; font-size: 12px;'>Delete</th>";
                    echo "</tr>";

                    $switch = false;

                    if (!empty($usersByType[$accountType])) {
                        foreach ($usersByType[$accountType] as $row) {
                            $bgColor = $switch ? '#F3F3F3' : '#E5E5E5';
                            $usernameRaw = $row['username'];
                            $usernameEsc = htmlspecialchars($usernameRaw);

                            // --- compute status ---
                            $statusText  = '';
                            $statusColor = '';

                            if (!empty($row['resetTimer'])) {
                                $resetTs   = strtotime($row['resetTimer']);
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
                            } else if ($row['passwordHash'] === '' || is_null($row['passwordHash'])) {
                                $statusText  = 'Password not set';
                                $statusColor = 'red';
                            }

                            echo "<tr>";
                            // username
                            echo "<td style='padding: 6px 12px; background-color: {$bgColor};'>" . $usernameEsc . "</td>";

                            // account link button
                            echo "<td style='text-align: center; background-color: {$bgColor};'>
                        <button style='border: none; background-color: transparent' 
                                onclick='copyLoginLink(" . json_encode(rawurlencode($usernameRaw)) . ", " . $accountTypeArg . ", this)'>&#128203</button>
                      </td>";

                            // reset link button
                            echo "<td style='text-align: center; background-color: {$bgColor};'>
                        <button style='border: none; background-color: transparent' 
                                onclick='copyResetLink(" . json_encode(rawurlencode($usernameRaw)) . ", this)'>&#128260</button>
                      </td>";

                            // status cell
                            $styleColor = $statusColor ? "color: {$statusColor};" : '';
                            echo "<td style='text-align: center; background-color: {$bgColor}; font-size: 12px; {$styleColor}'>"
                                . htmlspecialchars($statusText)
                                . "</td>";

                            // delete form cell
                            echo "<td style='text-align: center; background-color: {$bgColor};'>
                        <form method='POST' action='deleteAccount.php' style='display:inline;' data-name='" . $usernameEsc . "'>
                            <input type='hidden' name='deleteName' value='" . htmlspecialchars($usernameRaw) . "'>
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
                  </tr>";
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>
