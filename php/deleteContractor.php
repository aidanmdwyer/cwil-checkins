<?php
include 'accountProperties.php';

if (!accountProperties('Contractors')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to perform this action.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contractor_name'])) {
    require_once 'db.php';

    $name = $_POST['contractor_name'];

    $stmt3 = $conn->prepare("SELECT 1 FROM buildings WHERE ic = ? LIMIT 1;");
    $stmt3->bind_param("s", $name);
    $stmt3->execute();
    $stmt3->store_result();

    if($stmt3->num_rows === 0) {
        $stmt3->close();

        $stmt1 = $conn->prepare("DELETE FROM users WHERE username = ?");
        $stmt1->bind_param("s", $name);
        $stmt1->execute();

        if ($stmt1->errno) {
            // Unexpected DB error → stop here
            $stmt1->close();
            $conn->close();

            $errorMsg = "Database error deleting account: " . $stmt1->error;
            header("Location: ../php/addContractor.php?error=" . urlencode($errorMsg));
            exit();
        }

        $stmt2 = $conn->prepare("DELETE FROM contractors WHERE name = ?");
        $stmt2->bind_param("s", $name);
        $stmt2->execute();

        if ($stmt2->affected_rows > 0) {
            $stmt1->close();
            $stmt2->close();
            $conn->close();

            header("Location: ../php/addContractor.php?deleted=" . urlencode($name));
            exit();
        } else {
            $stmt1->close();
            $stmt2->close();
            $conn->close();

            $errorMsg = "Error deleting contractor.";
            header("Location: ../php/addContractor.php?error=" . urlencode($errorMsg));
            exit();
        }
    } else {
        $stmt3->close();
        $conn->close();

        $errorMsg = 'Remove or reassign all buildings assigned to "' . htmlspecialchars($name) . '" before deleting.';
        header("Location: ../php/addContractor.php?error=" . urlencode($errorMsg));
        exit();
    }
} else {
    $errorMsg = "Invalid request.";
    header("Location: ../php/addContractor.php?error=" . urlencode($errorMsg));
    exit();
}
?>
