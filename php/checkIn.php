<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    require_once 'db.php';

    $name = $_GET['name'];

    if(isset($name)) {
        $existsStmt = $conn->prepare("SELECT COUNT(*) FROM buildings WHERE name = ?");
        $existsStmt->bind_param("s", $name);
        $existsStmt->execute();
        $existsStmt->bind_result($count);
        $existsStmt->fetch();
        $existsStmt->close();

        if ($count > 0) {
            $chicagoTime = new DateTime('now', new DateTimeZone('America/Chicago'));
            $formattedTime = $chicagoTime->format('Y-m-d H:i:s');
            $insChecked = 1;

            $stmt = $conn->prepare("UPDATE buildings SET checked = ?, checkedTime = ? WHERE name = ?");
            $stmt->bind_param("iss", $insChecked, $formattedTime, $name);

            $conn->query("UPDATE sse_signal SET version = version + 1 WHERE id = 1");

            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: ../html/confirmation.html");
                exit;
            } else {
                $stmt->close();
                $conn->close();
                header("Location: ../html/checkInFailure.html");
                exit;
            }

        } else {
            $conn->close();
            header("Location: ../html/checkInFailure.html");
            exit;
        }
    } else {
        $conn->close();
        header("Location: ../html/checkInFailure.html");
        exit;
    }
}
?>
