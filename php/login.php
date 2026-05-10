<?php
session_start();

require_once 'db.php';

$page = basename($_SERVER['PHP_SELF']);

$username = $_GET['username'] ?? '';

//Logout handler
if (isset($_GET['logout']) && $_GET['logout'] === 'logout') {
    session_destroy();
    header("Location: " . $page);
    exit;
}

if(isset($_GET['passReset'])) {
    $passReset = $_GET['passReset'];
}

//Login handler
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, passwordHash, accountType FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $username, $passwordHash, $accountType);
        $stmt->fetch();

        if (password_verify($password, $passwordHash)) {
            $_SESSION['authenticated'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['accountType'] = $accountType;
            header("Location: " . $page);
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Username not found.";
    }
}

//If not authenticated, show login form
if (!isset($_SESSION['authenticated'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <title>Login</title>
        <link rel="stylesheet" href="/style.css">
        <link rel="manifest" href="/manifest.json">
    </head>
    <body style="display: flex; flex-direction: column; justify-content: flex-start; align-items: center; margin: 0; padding-top: 15vh;">
        <img src="/imgs/logo.png" style="width: 200px;">
        <h2>Login to see check-ins</h2>
        <form method="post">
            <label>Username:</label><br>
            <input type="text" id="username" name="username" style="width: 150px;" required autofocus value="<?php echo $username; ?>"><br><br>
            <label>Password:</label><br>
            <div style="position: relative; display: inline-block;">
                <input type="password" name="password" id="password" style="width: 150px;" required>
                <button type="button" id="togglePassword" style="position: absolute; right: 0; top: 0%; height: 100%; border: none; background-color: transparent;"><img src="/imgs/eyeShowing.png" style="height: 100%"></button>
            </div>
            <br><br>
            <button type="submit" class="big">Login</button>
        </form>
        <script>
            const togglePassword = document.getElementById('togglePassword');

            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.innerHTML = type === 'password' ? `<img src="/imgs/eyeShowing.png" style="height: 100%">` : `<img src="/imgs/eyeHidden.png" style="height: 100%">`;
            });
        </script>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>";
    else if(isset($passReset)) echo "<p style='color:green;'>$passReset</p>";
    ?>
    </body>
    </html>
    <?php
    exit;
}
?>