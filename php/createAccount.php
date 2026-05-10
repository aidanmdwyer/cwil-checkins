<?php
session_start();

require_once 'db.php';

$page = basename($_SERVER['PHP_SELF']);

//Handle account creation
if (isset($_POST['create_account']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['accountType'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $accountType = $_POST['accountType'];

    //Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        //Insert new user
        $insert = $conn->prepare("INSERT INTO users (username, passwordHash, accountType) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $username, $passwordHash, $accountType);
        if ($insert->execute()) {
            $_SESSION['authenticated'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['accountType'] = $accountType;
            header("Location: " . $page);
            exit;
        } else {
            $error = "Failed to create account. Try again.";
        }
    } else {
        $error = "Username already exists.";
    }
}

//check in page
if (!isset($_SESSION['authenticated'])) {
    $username = $_GET['username'] ? $_GET['username'] : '';
    $rawUsername = $_GET['username'] ? $_GET['username'] : '';
    $accountType = $_GET['accountType'] ?? '';

    if($accountType === 'contractor') {
        //Validate username against contractors table
        $stmt = $conn->prepare("SELECT name FROM contractors WHERE name = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
    } else if($accountType === 'manager') {
        //Validate username against managers table
        $stmt = $conn->prepare("SELECT name FROM managers WHERE name = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    include 'requireKey.php';
    $linkVerified = ($result->num_rows > 0 && $username !== '');

    if ($linkVerified) {
        //Check if user already exists in 'users' table
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $rawUsername);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            //Show account creation form
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <meta name="viewport"
                      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
                <title>Create Account</title>
                <link rel="stylesheet" href="/style.css">
                <link rel="manifest" href="/manifest.json">
            </head>
            <body style="display: flex; flex-direction: column; justify-content: flex-start; align-items: center; margin: 0; padding-top: 15vh;">
            <img src="/imgs/logo.png" style="width: 200px;">
            <h2>Create <?php echo $accountType; ?> account</h2>
            <form method="post" onsubmit="return validatePasswords()">
                <label>Username:</label><br>
                <input type="text" name="username" id="username" style="width: 150px;" required
                    <?php echo ($accountType === 'admin' || $accountType === 'developer') ? '' : 'readonly'; ?>
                       value="<?php echo $username; ?>">
                <br><br>

                <label>Password:</label><br>
                <div style="position: relative; display: inline-block;">
                    <input type="password" name="password" id="password" style="width: 150px;" required>
                    <button type="button" id="togglePassword" style="position: absolute; right: 0; top: 0%; height: 100%; border: none; background-color: transparent;"><img src="/imgs/eyeShowing.png" style="height: 100%"></button>
                </div>
                <br><br>

                <label>Confirm Password:</label><br>
                <input type="password" name="confirm_password" id="confirm_password" style="width: 150px;" required>
                <br><br>

                <input type="hidden" name="accountType" value="<?php echo $accountType; ?>">
                <button type="submit" name="create_account" class="big">Create Account</button>
            </form>

            <script>
                const password = document.getElementById('password');
                const confirmPassword = document.getElementById('confirm_password');
                const togglePassword = document.getElementById('togglePassword');

                togglePassword.addEventListener('click', function() {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    confirmPassword.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? `<img src="/imgs/eyeShowing.png" style="height: 100%">` : `<img src="/imgs/eyeHidden.png" style="height: 100%">`;
                });

                function validatePasswords() {
                    const pass = password.value;

                    const minLength   = /.{8,}/;
                    const upperCase   = /[A-Z]/;
                    const lowerCase   = /[a-z]/;
                    const number      = /[0-9]/;

                    if (pass !== confirmPassword.value) {
                        alert('Passwords do not match!');
                        confirmPassword.focus();
                        return false;
                    }
                    if (!minLength.test(pass)) {
                        alert("Password must be at least 8 characters long.");
                        password.focus();
                        return false;
                    }
                    if (!upperCase.test(pass)) {
                        alert("Password must contain at least one uppercase letter.");
                        password.focus();
                        return false;
                    }
                    if (!lowerCase.test(pass)) {
                        alert("Password must contain at least one lowercase letter.");
                        password.focus();
                        return false;
                    }
                    if (!number.test(pass)) {
                        alert("Password must contain at least one number.");
                        password.focus();
                        return false;
                    }

                    return true;
                }
            </script>

            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            </body>
            </html>
            <?php
            exit;
        } else {
            header("Location: /index.php?username=" . rawurlencode($username));
            exit;
        }
    } else {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport"
                  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
            <title>Create Account</title>
            <link rel="stylesheet" href="/style.css">
            <link rel="manifest" href="/manifest.json">
        </head>
        <body style="display: flex; flex-direction: column; justify-content: flex-start; align-items: center; margin: 0; padding-top: 15vh;">
        <img src="/imgs/logo.png" style="width: 200px;">
        <h2>Create an account</h2>
        <p>Please access this page from a link provided by your City Wide point of contact.</p>
        </body>
        </html>
        <?php
        exit;
    }
} else {
    //If authenticated, redirect to dashboard
    header("Location: /index.php");
    exit;
}
?>