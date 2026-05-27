<?php
include 'requireKey.php'; isKeyValid();

include 'accountProperties.php';

if (!accountProperties('Import Buildings')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to perform this action.');
}

header('Content-Type: application/json');

//read and decode JSON payload
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

if (!is_array($data['importData']) || !isset($data['commit'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);  //frontend will fall into catch block
    exit;
}

$commitMode = $data['commit'];
$importData = $data['importData'];

require_once 'db.php';

$insertStmt = null;
if($commitMode) {
    $insertStmt = $conn->prepare(
        "INSERT INTO buildings
              (name, manager, ic, monday, tuesday, wednesday, thursday, friday, saturday, sunday, active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
              manager     = VALUES(manager),
              ic          = VALUES(ic),
              monday      = VALUES(monday),
              tuesday     = VALUES(tuesday),
              wednesday   = VALUES(wednesday),
              thursday    = VALUES(thursday),
              friday      = VALUES(friday),
              saturday    = VALUES(saturday),
              sunday      = VALUES(sunday),
              active      = VALUES(active)"
    );
    if (!$insertStmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Prepare failed: '.$conn->error]);
        exit;
    }
}

$existingManagersQuery = $conn->query("SELECT name FROM managers");
$existingManagers = [];
while($row = $existingManagersQuery->fetch_assoc()) {
    $existingManagers[] = $row['name'];
}

$existingContractorsQuery = $conn->query("SELECT name FROM contractors");
$existingContractors = [];
while($row = $existingContractorsQuery->fetch_assoc()) {
    $existingContractors[] = $row['name'];
}

$results = [];
$missingManagers = [];
$missingIcs = [];
$errorCount = 0;
$managerErrorCount = 0;
$icErrorCount = 0;
$vendorCardErrorCount = 0;
$dayErrors = [];
$otherErrorCount = 0;
$numSuccesses = 0;
$numRows = 0;
$numUpdated = 0;
$numExist = 0;
$previewBuildings = [];

//loop through import data
foreach ($importData as $row) {
    $rowName = $row['name'];
    $rowIc = $row['ic'];
    $rowManager = $row['manager'];
    $rowMonday = (int)$row['monday'];
    $rowTuesday = (int)$row['tuesday'];
    $rowWednesday = (int)$row['wednesday'];
    $rowThursday = (int)$row['thursday'];
    $rowFriday = (int)$row['friday'];
    $rowSaturday = (int)$row['saturday'];
    $rowSunday = (int)$row['sunday'];
    $active = $rowMonday === 1 || $rowTuesday === 1 || $rowWednesday === 1 || $rowThursday === 1 || $rowFriday === 1 || $rowSaturday === 1 || $rowSunday === 1;

    $numRows++;
    $doCommit = $commitMode;

    //validate manager and IC
    $managerExists = in_array($rowManager, $existingManagers);
    $icExists = in_array($rowIc, $existingContractors);

    $rowErrors = [];

    if($rowIc[0] === '*') {
        $rowErrors[] = 'Vendor Card';
        $vendorCardErrorCount++;
    } else if (!$icExists) {
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
        $results[] = ['color' => 'red', 'message' => implode(', ', $rowErrors)];
        continue;
    }

    if($doCommit) { //commit mode
        $insertStmt->bind_param(
            'sssiiiiiiii',
            $rowName, $rowManager, $rowIc,
            $rowMonday, $rowTuesday, $rowWednesday, $rowThursday, $rowFriday, $rowSaturday, $rowSunday,
            $active
        );
        if ($insertStmt->execute()) {
            if ($insertStmt->affected_rows === 1) {
                if(!$active) {
                    $results[] = ['color' => 'orange', 'message' => "Inserted Deactivated"];
                    $dayErrors[] = $rowName;
                } else {
                    $results[] = ['color' => 'green', 'message' => 'Inserted'];
                    $numSuccesses++;
                }
            } elseif ($insertStmt->affected_rows === 2) {
                if(!$active) {
                    $results[] = ['color' => 'orange', 'message' => "Inserted Deactivated, Updated"];
                    $dayErrors[] = $rowName;
                } else {
                    $results[] = ['color' => 'orange', 'message' => 'Updated'];
                }
                $numUpdated++;
            } else {
                $results[] = ['color' => 'orange', 'message' => 'Already Exists'];
                $numExist++;
            }
        } else {
            $results[] = ['color' => 'red', 'message' => $insertStmt->error];
            $errorCount++;
            $otherErrorCount++;
        }
    } else if (!$commitMode) { //preview mode

        $existingBuildingsQuery = $conn->query("SELECT name, manager, ic, monday, tuesday, wednesday, thursday, friday, saturday, sunday FROM buildings");
        $existingBuildings = [];
        while($row = $existingBuildingsQuery->fetch_assoc()) {
            $existingBuildings[$row['name']] = $row;
        }

        if(isset($previewBuildings[$row['name']])) {
            if($previewBuildings[$row['name']] === $row) {
                //exact duplicate in import
                $results[] = ['color' => 'orange', 'message' => 'Would Already Exist'];
                $numExist++;
            } else {
                //duplicate name in import, different data
                $results[] = ['color' => 'orange', 'message' => 'Would Update'];
                $numUpdated++;
            }
        } else if(isset($existingBuildings[$row['name']])) {
            //row with same name exists in db, decide if it would update or not
            $existingBuilding = $existingBuildings[$row['name']];
            $wouldChange =
                ($existingBuilding['manager'] !== $rowManager) ||
                ($existingBuilding['ic'] !== $rowIc) ||
                ((int)$existingBuilding['monday'] !== $rowMonday) ||
                ((int)$existingBuilding['tuesday'] !== $rowTuesday) ||
                ((int)$existingBuilding['wednesday'] !== $rowWednesday) ||
                ((int)$existingBuilding['thursday'] !== $rowThursday) ||
                ((int)$existingBuilding['friday'] !== $rowFriday) ||
                ((int)$existingBuilding['saturday'] !== $rowSaturday) ||
                ((int)$existingBuilding['sunday'] !== $rowSunday);

            if ($wouldChange) {
                $results[] = ['color' => 'orange', 'message' => 'Would Update'];
                $numUpdated++;
            } else {
                $results[] = ['color' => 'orange', 'message' => 'Would Already Exist'];
                $numExist++;
            }
        } else {
            if(!$active) {
                $results[] = ['color' => 'orange', 'message' => "Would Insert Deactivated"];
                $dayErrors[] = $rowName;
            } else {
                //no row with same name already in db, would insert
                $results[] = ['color' => 'green', 'message' => 'Would Insert'];
                $numSuccesses++;
            }
        }

        //add this building to preview buildings
        $previewBuildings[$row['name']] = $row;
    }
}

$output = [
    'results' => $results,
    'missingManagers' => $missingManagers,
    'missingIcs' => $missingIcs,
    'errorCount' => $errorCount,
    'managerErrorCount' => $managerErrorCount,
    'icErrorCount' => $icErrorCount,
    'vendorCardErrorCount' => $vendorCardErrorCount,
    'otherErrorCount' => $otherErrorCount,
    'numSuccesses' => $numSuccesses,
    'numRows' => $numRows,
    'numUpdated' => $numUpdated,
    'numExist' => $numExist,
    'dayErrors'  => $dayErrors,
];

$conn->close();

//hand the array back to JS
echo json_encode($output);