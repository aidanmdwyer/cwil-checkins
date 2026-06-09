<?php
include 'accountProperties.php';

if (!accountProperties('Select/Edit Multiple Buildings')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to perform this action.');
}

require_once 'db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    $checkedNames = $_POST['checkedNames'] ?? [];

    switch ($action) {
        case 'delete':
            if (!empty($checkedNames)) {
                $placeholders = implode(',', array_fill(0, count($checkedNames), '?'));

                $stmt = $conn->prepare("DELETE FROM `buildings` WHERE `buildings`.`name` IN ($placeholders)");

                $types = str_repeat('s', count($checkedNames));
                $stmt->bind_param($types, ...$checkedNames);

                $stmt->execute();
                $stmt->close();
            }
            $conn->close();
            exit;

        case 'print':
            header('Content-Type: application/json');
            echo json_encode($checkedNames);
            $conn->close();
            exit;

        case 'changeIC':
            $changeIC = $_POST['changeIC'];

            //Validate contractor exists
            $check = $conn->prepare("SELECT COUNT(*) FROM contractors WHERE name = ?");
            $check->bind_param("s", $changeIC);
            $check->execute();
            $check->bind_result($exists);
            $check->fetch();
            $check->close();

            if ($exists == 0) {
                echo json_encode(['updated' => $checkedNames, 'status' => 'error', 'message' => 'Contractor "' . htmlspecialchars($changeIC) . '" does not exist.']);
                exit;
            }

            if (!empty($checkedNames) && isset($changeIC)) {
                $placeholders = implode(',', array_fill(0, count($checkedNames), '?'));

                $stmt = $conn->prepare("UPDATE `buildings` SET `ic` = ? WHERE `name` IN ($placeholders)");

                $types = 's' . str_repeat('s', count($checkedNames));
                $stmt->bind_param($types, $changeIC, ...$checkedNames);

                $stmt->execute();
                $stmt->close();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['updated' => $checkedNames, 'status' => 'error', 'message' => 'No buildings are selected.']);
                $conn->close();
                exit;
            }

            header('Content-Type: application/json');
            echo json_encode(['updated' => $checkedNames, 'status' => 'success', 'message' => 'Successfully assigned ' . count($checkedNames) . ' building(s) to "' . $changeIC . '".']);
            $conn->close();
            exit;

        case 'changeManager':

            $changeManager = $_POST['changeManager'];

            if($changeManager === "---" || $changeManager === "") {
                echo json_encode(['updated' => $checkedNames, 'status' => 'error', 'message' => 'Manager cannot be empty.']);
                exit;
            }

            if (!empty($checkedNames) && isset($changeManager)) {
                $placeholders = implode(',', array_fill(0, count($checkedNames), '?'));

                $stmt = $conn->prepare("UPDATE `buildings` SET `manager` = ? WHERE `name` IN ($placeholders)");

                $types = 's' . str_repeat('s', count($checkedNames));
                $stmt->bind_param($types, $changeManager, ...$checkedNames);

                $stmt->execute();
                $stmt->close();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['updated' => $checkedNames, 'status' => 'error', 'message' => 'No buildings are selected.']);
                $conn->close();
                exit;
            }

            header('Content-Type: application/json');
            echo json_encode(['updated' => $checkedNames, 'status' => 'success', 'message' => 'Successfully assigned ' . count($checkedNames) . ' building(s) to "' . $changeManager . '".']);
            $conn->close();
            exit;

        case 'activate':

            if (!empty($checkedNames)) {
                $placeholders = implode(',', array_fill(0, count($checkedNames), '?'));

                $stmt = $conn->prepare("UPDATE `buildings` SET `active` = 1 WHERE `name` IN ($placeholders)");

                $types = str_repeat('s', count($checkedNames));
                $stmt->bind_param($types, ...$checkedNames);

                $stmt->execute();
                $stmt->close();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['updated' => $checkedNames, 'status' => 'error', 'message' => 'No buildings are selected.']);
                $conn->close();
                exit;
            }

            header('Content-Type: application/json');
            echo json_encode(['updated' => $checkedNames, 'status' => 'success', 'message' => 'Successfully activated ' . count($checkedNames) . ' building(s).']);
            $conn->close();
            exit;

        case 'deactivate':

            if (!empty($checkedNames)) {
                $placeholders = implode(',', array_fill(0, count($checkedNames), '?'));

                $stmt = $conn->prepare("UPDATE `buildings` SET `active` = 0 WHERE `name` IN ($placeholders)");

                $types = str_repeat('s', count($checkedNames));
                $stmt->bind_param($types, ...$checkedNames);

                $stmt->execute();
                $stmt->close();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['updated' => $checkedNames, 'status' => 'error', 'message' => 'No buildings are selected.']);
                $conn->close();
                exit;
            }

            header('Content-Type: application/json');
            echo json_encode(['updated' => $checkedNames, 'status' => 'success', 'message' => 'Successfully deactivated ' . count($checkedNames) . ' building(s).']);
            $conn->close();
            exit;

        case 'checkAll':
            if (!empty($checkedNames)) {
                $placeholders = implode(',', array_fill(0, count($checkedNames), '?'));

                $stmt = $conn->prepare("UPDATE `buildings` SET `checked` = 1, checkedTime = ? WHERE `name` IN ($placeholders)");

                $chicagoTime = new DateTime('now', new DateTimeZone('America/Chicago'));
                $formattedTime = $chicagoTime->format('Y-m-d H:i:s');

                $types = 's' . str_repeat('s', count($checkedNames));
                $stmt->bind_param($types, $formattedTime, ...$checkedNames);

                $stmt->execute();
                $stmt->close();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['updated' => $checkedNames, 'status' => 'error', 'message' => 'No buildings are selected.']);
                $conn->close();
                exit;
            }

            $conn->query("UPDATE sse_signal SET version = version + 1 WHERE id = 1");

            header('Content-Type: application/json');
            echo json_encode(['updated' => $checkedNames, 'status' => 'success', 'message' => 'Successfully checked ' . count($checkedNames) . ' building(s).']);
            $conn->close();
            exit;

        case 'uncheckAll':
            if (!empty($checkedNames)) {
                $placeholders = implode(',', array_fill(0, count($checkedNames), '?'));

                $stmt = $conn->prepare("UPDATE `buildings` SET `checked` = 0, checkedTime = ? WHERE `name` IN ($placeholders)");

                $null = null;

                $types = 's' . str_repeat('s', count($checkedNames));
                $stmt->bind_param($types, $null, ...$checkedNames);

                $stmt->execute();
                $stmt->close();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['updated' => $checkedNames, 'status' => 'error', 'message' => 'No buildings are selected.']);
                $conn->close();
                exit;
            }

            $conn->query("UPDATE sse_signal SET version = version + 1 WHERE id = 1");

            header('Content-Type: application/json');
            echo json_encode(['updated' => $checkedNames, 'status' => 'success', 'message' => 'Successfully unchecked ' . count($checkedNames) . ' building(s).']);
            $conn->close();
            exit;

        default:
            header('Content-Type: application/json', true, 400);
            echo json_encode(['error' => 'Invalid action']);
            $conn->close();
            exit;
    }
}

$conn->close();
?>