<?php
include 'accountProperties.php';

if (!accountProperties('Select')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to perform this action.');
}

require_once 'db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    $checkedIds = $_POST['checkedIds'] ?? [];

    switch ($action) {
        case 'delete':
            if (!empty($checkedIds)) {
                $placeholders = implode(',', array_fill(0, count($checkedIds), '?'));

                $stmt = $conn->prepare("DELETE FROM `buildings` WHERE `buildings`.`id` IN ($placeholders)");

                $types = str_repeat('s', count($checkedIds));
                $stmt->bind_param($types, ...$checkedIds);

                $stmt->execute();
                $stmt->close();
            }
            $conn->close();
            exit;

        case 'print':
            header('Content-Type: application/json');
            echo json_encode($checkedIds);
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
                echo json_encode(['updated' => $checkedIds, 'status' => 'error', 'message' => 'Contractor "' . htmlspecialchars($changeIC) . '" does not exist.']);
                exit;
            }

            if (!empty($checkedIds) && isset($changeIC)) {
                $placeholders = implode(',', array_fill(0, count($checkedIds), '?'));

                $stmt = $conn->prepare("UPDATE `buildings` SET `ic` = ? WHERE `id` IN ($placeholders)");

                $types = 's' . str_repeat('s', count($checkedIds));
                $stmt->bind_param($types, $changeIC, ...$checkedIds);

                $stmt->execute();
                $stmt->close();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['updated' => $checkedIds, 'status' => 'error', 'message' => 'No buildings are selected.']);
                $conn->close();
                exit;
            }

            header('Content-Type: application/json');
            echo json_encode(['updated' => $checkedIds, 'status' => 'success', 'message' => 'Successfully assigned ' . count($checkedIds) . ' building(s) to "' . $changeIC . '".']);
            $conn->close();
            exit;

        case 'changeManager':

            $changeManager = $_POST['changeManager'];

            if($changeManager === "---" || $changeManager === "") {
                echo json_encode(['updated' => $checkedIds, 'status' => 'error', 'message' => 'Manager cannot be empty.']);
                exit;
            }

            if (!empty($checkedIds) && isset($changeManager)) {
                $placeholders = implode(',', array_fill(0, count($checkedIds), '?'));

                $stmt = $conn->prepare("UPDATE `buildings` SET `manager` = ? WHERE `id` IN ($placeholders)");

                $types = 's' . str_repeat('s', count($checkedIds));
                $stmt->bind_param($types, $changeManager, ...$checkedIds);

                $stmt->execute();
                $stmt->close();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['updated' => $checkedIds, 'status' => 'error', 'message' => 'No buildings are selected.']);
                $conn->close();
                exit;
            }

            header('Content-Type: application/json');
            echo json_encode(['updated' => $checkedIds, 'status' => 'success', 'message' => 'Successfully assigned ' . count($checkedIds) . ' building(s) to "' . $changeManager . '".']);
            $conn->close();
            exit;

        case 'activate':

            if (!empty($checkedIds)) {
                $placeholders = implode(',', array_fill(0, count($checkedIds), '?'));

                $stmt = $conn->prepare("UPDATE `buildings` SET `active` = 1 WHERE `id` IN ($placeholders)");

                $types = str_repeat('s', count($checkedIds));
                $stmt->bind_param($types, ...$checkedIds);

                $stmt->execute();
                $stmt->close();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['updated' => $checkedIds, 'status' => 'error', 'message' => 'No buildings are selected.']);
                $conn->close();
                exit;
            }

            header('Content-Type: application/json');
            echo json_encode(['updated' => $checkedIds, 'status' => 'success', 'message' => 'Successfully activated ' . count($checkedIds) . ' building(s).']);
            $conn->close();
            exit;

        case 'deactivate':

            if (!empty($checkedIds)) {
                $placeholders = implode(',', array_fill(0, count($checkedIds), '?'));

                $stmt = $conn->prepare("UPDATE `buildings` SET `active` = 0 WHERE `id` IN ($placeholders)");

                $types = str_repeat('s', count($checkedIds));
                $stmt->bind_param($types, ...$checkedIds);

                $stmt->execute();
                $stmt->close();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['updated' => $checkedIds, 'status' => 'error', 'message' => 'No buildings are selected.']);
                $conn->close();
                exit;
            }

            header('Content-Type: application/json');
            echo json_encode(['updated' => $checkedIds, 'status' => 'success', 'message' => 'Successfully deactivated ' . count($checkedIds) . ' building(s).']);
            $conn->close();
            exit;

        case 'checkAll':
            if (!empty($checkedIds)) {
                $placeholders = implode(',', array_fill(0, count($checkedIds), '?'));

                $stmt = $conn->prepare("UPDATE `buildings` SET `checked` = 1, checkedTime = ? WHERE `id` IN ($placeholders)");

                $chicagoTime = new DateTime('now', new DateTimeZone('America/Chicago'));
                $formattedTime = $chicagoTime->format('Y-m-d H:i:s');

                $types = 's' . str_repeat('s', count($checkedIds));
                $stmt->bind_param($types, $formattedTime, ...$checkedIds);

                $stmt->execute();
                $stmt->close();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['updated' => $checkedIds, 'status' => 'error', 'message' => 'No buildings are selected.']);
                $conn->close();
                exit;
            }

            $conn->query("UPDATE sse_signal SET version = version + 1 WHERE id = 1");

            header('Content-Type: application/json');
            echo json_encode(['updated' => $checkedIds, 'status' => 'success', 'message' => 'Successfully checked ' . count($checkedIds) . ' building(s).']);
            $conn->close();
            exit;

        case 'uncheckAll':
            if (!empty($checkedIds)) {
                $placeholders = implode(',', array_fill(0, count($checkedIds), '?'));

                $stmt = $conn->prepare("UPDATE `buildings` SET `checked` = 0, checkedTime = ? WHERE `id` IN ($placeholders)");

                $null = null;

                $types = 's' . str_repeat('s', count($checkedIds));
                $stmt->bind_param($types, $null, ...$checkedIds);

                $stmt->execute();
                $stmt->close();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['updated' => $checkedIds, 'status' => 'error', 'message' => 'No buildings are selected.']);
                $conn->close();
                exit;
            }

            $conn->query("UPDATE sse_signal SET version = version + 1 WHERE id = 1");

            header('Content-Type: application/json');
            echo json_encode(['updated' => $checkedIds, 'status' => 'success', 'message' => 'Successfully unchecked ' . count($checkedIds) . ' building(s).']);
            $conn->close();
            exit;

        case 'updateAll':
            if (empty($checkedIds)) {
                header('Content-Type: application/json');
                echo json_encode([
                    'updated' => [],
                    'status' => 'error',
                    'message' => 'No buildings are selected.'
                ]);
                $conn->close();
                exit;
            }

            // Build placeholders for selected IDs
            $placeholders = implode(',', array_fill(0, count($checkedIds), '?'));
            $types = str_repeat('s', count($checkedIds)); // <-- integers!

            // Select only those buildings
            $sql = "SELECT id, name FROM `buildings` WHERE id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$checkedIds);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $update = $conn->prepare("UPDATE `buildings` SET `name` = ? WHERE `id` = ?");
                $count = 0;

                while ($row = $result->fetch_assoc()) {
                    $id = $row['id'];
                    $original = $row['name'];
                    $decoded = html_entity_decode($original, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                    // convert to clean UTF-8
                    $decoded = mb_convert_encoding($decoded, 'UTF-8', 'UTF-8');

                    if ($decoded !== $original) {
                        $u = $conn->prepare("UPDATE buildings SET name = ? WHERE id = ?");
                        $u->bind_param('ss', $decoded, $id);
                        $u->execute();
                        $u->close();
                    }
                }

                $update->close();
                $stmt->close();

                header('Content-Type: application/json');
                echo json_encode([
                    'updated' => $checkedIds,
                    'status' => 'success',
                    'message' => "Successfully decoded {$count} building(s)."
                ]);
                $conn->close();
                exit;
            } else {
                $stmt->close();
                header('Content-Type: application/json');
                echo json_encode([
                    'updated' => $checkedIds,
                    'status' => 'error',
                    'message' => 'No matching buildings found or no updates were needed.'
                ]);
                $conn->close();
                exit;
            }

        default:
            header('Content-Type: application/json', true, 400);
            echo json_encode(['error' => 'Invalid action']);
            $conn->close();
            exit;
    }
}

$conn->close();
?>