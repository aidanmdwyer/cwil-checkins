<?php
include 'requireKey.php'; isKeyValid();

include 'accountProperties.php';

if (!accountProperties('Import Buildings')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to perform this action.');
}

header('Content-Type: application/json');

/* --- 1. read and decode JSON payload --- */
$payload = file_get_contents('php://input');
$data    = json_decode($payload, true);

if (!is_array($data['records']) || !isset($data['mode'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);  //front‑end will fall into catch{}
    exit;
}

$mode = $data['mode'];
$records = $data['records'];
$commitMode = ($mode === 'commit');

require_once 'db.php';

$insertStmt = null;
if ($commitMode) {
    $sql = "INSERT INTO buildings
              (id, name, manager, ic, checked, checkedTime,
               monday, tuesday, wednesday, thursday, friday, saturday, sunday)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
              manager     = VALUES(manager),
              ic          = VALUES(ic),
              checked     = VALUES(checked),
              checkedTime = VALUES(checkedTime),
              monday      = VALUES(monday),
              tuesday     = VALUES(tuesday),
              wednesday   = VALUES(wednesday),
              thursday    = VALUES(thursday),
              friday      = VALUES(friday),
              saturday    = VALUES(saturday),
              sunday      = VALUES(sunday)";
    $insertStmt = $conn->prepare($sql);
    if (!$insertStmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Prepare failed: '.$conn->error]);
        exit;
    }
}

/* Preview path: look up existing row to decide would-insert / would-update / already-exists */
$selectExisting = null;
if (!$commitMode) {
    $selectExisting = $conn->prepare(
        "SELECT manager, ic, checked, checkedTime,
                monday, tuesday, wednesday, thursday, friday, saturday, sunday
         FROM buildings WHERE name = ? LIMIT 1"
    );
    if (!$selectExisting) {
        http_response_code(500);
        echo json_encode(['error' => 'Prepare failed: '.$conn->error]);
        exit;
    }
}

$checkManager = $conn->prepare("SELECT 1 FROM managers WHERE name = ? LIMIT 1");
$checkIc = $conn->prepare("SELECT 1 FROM contractors WHERE name = ? LIMIT 1");

/* --- 4. loop through records --- */
$results = [];
$missingManagers = [];
$missingIcs = [];
$errorCount = 0;
$managerErrorCount = 0;
$icErrorCount = 0;
$dayErrors = [];
$otherErrorCount = 0;
$numSuccesses = 0;
$numRows = 0;
$numUpdated = 0;
$numExist = 0;
$previewBuildings = [];

foreach ($records as $row) {
    $doCommit = $commitMode;
    $numRows++;

    $rowName = $row['name'];
    $rowIc = $row['ic'];
    $rowManager = $row['manager'];
    $checked = 0;    //FALSE
    $checked_time = null; //NULL
    $m = (int)$row['monday'];
    $tu = (int)$row['tuesday'];
    $w = (int)$row['wednesday'];
    $th = (int)$row['thursday'];
    $f = (int)$row['friday'];
    $sa = (int)$row['saturday'];
    $su = (int)$row['sunday'];

    // Validate manager
    $checkManager->bind_param("s", $rowManager);
    $checkManager->execute();
    $checkManager->store_result();
    $managerExists = $checkManager->num_rows > 0;

    // Validate IC
    $checkIc->bind_param("s", $rowIc);
    $checkIc->execute();
    $checkIc->store_result();
    $icExists = $checkIc->num_rows > 0;

    $rowErrors = [];


    if($m === 0 &&  $tu === 0 && $w === 0 && $th === 0 && $f === 0 && $sa === 0 && $su === 0) {
        $rowErrors[] = 'No Days';
        $dayErrors[] = $rowName;
    }

    if (!$icExists) {
        $rowErrors[] = 'Contractor DNE';
        if (!in_array($rowIc, $missingIcs)) {
            $missingIcs[] = $rowIc;
        }
        $icErrorCount++;
    }

    if (!$managerExists) {
        $rowErrors[] = 'Manager DNE';
        if (!in_array($rowManager, $missingManagers)) {
            $missingManagers[] = $rowManager;
        }
        $managerErrorCount++;
    }

    if (!empty($rowErrors)) {
        $errorCount++;
        $doCommit = false;
        $results[] = ['success' => false, 'error' => implode(', ', $rowErrors)];
        continue;
    }

    if($doCommit) {
        $insId = bin2hex(random_bytes(8));
        $insertStmt->bind_param(
            'ssssisiiiiiii',
            $insId,
            $rowName,
            $rowManager,
            $rowIc,
            $checked,
            $checked_time,
            $m, $tu, $w, $th, $f, $sa, $su
        );

        if ($insertStmt->execute()) {
            if ($insertStmt->affected_rows === 1) {
                $results[] = ['success' => true, 'message' => 'Inserted'];
                $numSuccesses++;
            } elseif ($insertStmt->affected_rows === 2) {
                $results[] = ['success' => true, 'message' => 'Updated'];
                $numUpdated++;
            } else {
                $results[] = ['success' => true, 'message' => 'Already Exists'];
                $numExist++;
            }
        } else {
            $results[] = ['success' => false, 'error' => $insertStmt->error];
            $errorCount++;
            $otherErrorCount++;
        }
    } else if (!$commitMode) {
        //Preview mode
        $selectExisting->bind_param("s", $rowName);
        $selectExisting->execute();
        $selectExisting->store_result();

        $thisBuilding = [];
        $thisBuilding['manager'] =  $rowManager;
        $thisBuilding['ic'] = $rowIc;
        $thisBuilding['m'] = $row['monday'];
        $thisBuilding['tu'] = $row['tuesday'];
        $thisBuilding['w'] = $row['wednesday'];
        $thisBuilding['th'] = $row['thursday'];
        $thisBuilding['f'] = $row['friday'];
        $thisBuilding['sa'] = $row['saturday'];
        $thisBuilding['su'] = $row['sunday'];

        if (in_array([$row['name'] => $thisBuilding], $previewBuildings)) {
            $results[] = ['success' => true, 'message' => 'Already Exists'];
            $numExist++;
        } else if(isset($previewBuildings[$row['name']])) {
            $results[] = ['success' => true, 'message' => 'Would Update'];
            $numUpdated++;
        } else if ($selectExisting->num_rows === 0) {
            // Would insert (no row with same UNIQUE/PK key, typically 'name')
            $results[] = ['success' => true, 'message' => 'Would Insert'];
            $numSuccesses++;
        } else {
            // Row exists → decide if it would update or no-op
            $selectExisting->bind_result(
                $exManager, $exIc, $exChecked, $exCheckedTime,
                $exM, $exTu, $exW, $exTh, $exF, $exSa, $exSu
            );
            $selectExisting->fetch();

            $wouldChange =
                ($exManager !== $rowManager) ||
                ($exIc !== $rowIc) ||
                ((int)$exM !== $m) ||
                ((int)$exTu !== $tu) ||
                ((int)$exW !== $w) ||
                ((int)$exTh !== $th) ||
                ((int)$exF !== $f) ||
                ((int)$exSa !== $sa) ||
                ((int)$exSu !== $su);

            if ($wouldChange) {
                $results[] = ['success' => true, 'message' => 'Would Update'];
                $numUpdated++;
            } else {
                $results[] = ['success' => true, 'message' => 'Already Exists'];
                $numExist++;
            }
        }

        $selectExisting->free_result();

        $previewBuildings[$row['name']] = $thisBuilding;
    }
}

$output = [
    'results' => $results,
    'missingManagers' => $missingManagers,
    'missingIcs' => $missingIcs,
    'errorCount' => $errorCount,
    'managerErrorCount' => $managerErrorCount,
    'icErrorCount' => $icErrorCount,
    'otherErrorCount' => $otherErrorCount,
    'numSuccesses' => $numSuccesses,
    'numRows' => $numRows,
    'numUpdated' => $numUpdated,
    'numExist' => $numExist,
    'dayErrors'  => $dayErrors,
];

$conn->close();

/* --- 5. hand the array back to JS --- */
echo json_encode($output);