<?php
session_start();

require_once 'db.php';

$username = $_GET['username'] ?? '';
$message = "";

// Process POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['password']) && !empty($_POST['username'])) {
    $newPassword = $_POST['password'];
    $username = $_POST['username'];

    // Get current hash
    $stmt = $conn->prepare("SELECT passwordHash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($currentHash);
    if ($stmt->fetch()) {
        if (password_verify($newPassword, $currentHash)) {
            $message = "<p style='color: red;'>Password update failed — new password is the same as the old one.</p>";
        } else {
            $stmt->close();
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // 🔹 Update password AND clear resetTimer
            $stmt = $conn->prepare("UPDATE users SET passwordHash = ?, resetTimer = NULL WHERE username = ?");
            $stmt->bind_param("ss", $hashedPassword, $username);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $stmt->close();
                header('Location: /index.php?username=' . urlencode($username) . '&passReset=' . urlencode('Password reset successful!'));
            } else {
                $message = "<p style='color: red;'>Password update failed.</p>";
            }
        }
    }
    $stmt->close();
}

// 🔹 Check if user exists AND resetTimer is within 24 hours
$stmt = $conn->prepare("SELECT id, resetTimer FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

$stmt->bind_result($userId, $resetTimer);
$stmt->fetch();

if ($stmt->num_rows > 0 && $resetTimer !== null) {
    $resetValid = (strtotime($resetTimer) >= strtotime('-23 hours'));

    if ($resetValid) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Password</title>
            <link rel="stylesheet" href="/style.css">
            <link rel="manifest" href="/manifest.json">
        </head>
        <body style="display: flex; flex-direction: column; justify-content: flex-start; align-items: center; margin: 0; padding-top: 15vh;">
        <img src="/imgs/logo.png" style="width: 200px;">
        <h2>Reset Password</h2>
        <form method="post" onsubmit="return validatePasswords()">
            <label>Username:</label><br>
            <input type="text" name="username" style="width: 150px;" required readonly value="<?php echo htmlspecialchars($username); ?>"><br><br>

            <label>New Password:</label><br>
            <div style="position: relative; display: inline-block;">
                <input type="password" name="password" id="password" style="width: 150px;" required>
                <button type="button" id="togglePassword" style="position: absolute; right: 0; top: 0%; height: 100%; border: none; background-color: transparent;"><img src="/imgs/eyeShowing.png" style="height: 100%"></button>
            </div>
            <br><br>

            <label>Confirm New Password:</label><br>
            <input type="password" name="confirm_password" id="confirm_password" style="width: 150px;" required>
            <br><br>

            <button type="submit" class="big">Confirm</button>
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

        <div style="margin-top: 20px;">
            <?php echo $message; ?>
        </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// If no user OR resetTimer expired
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="stylesheet" href="/style.css">
    <link rel="manifest" href="/manifest.json">
</head>
<body style="text-align: center">
<br>
<img src="/imgs/logo.png" style="width: 200px;">
<br>
<h2>Reset Password</h2>
<p>Please access this page from a link provided by your City Wide point of contact.</p>
</body>
</html>