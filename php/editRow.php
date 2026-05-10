<?php
include 'accountProperties.php';

if (!accountProperties('Edit')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to perform this action.');
}

header('Content-Type: application/json');

require_once 'db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $editId = $_POST['editId'];
    $insName = $_POST['name'];
    $maxLength = 80;
    $insManager = $_POST['manager'];
    $insIc = $_POST['contractor'];
    $monday = $_POST['monday'] ? true : false;
    $tuesday = $_POST['tuesday'] ? true : false;
    $wednesday = $_POST['wednesday'] ? true : false;
    $thursday = $_POST['thursday'] ? true : false;
    $friday = $_POST['friday'] ? true : false;
    $saturday = $_POST['saturday'] ? true : false;
    $sunday = $_POST['sunday'] ? true : false;

    //Validate contractor exists
    $check = $conn->prepare("SELECT COUNT(*) FROM contractors WHERE name = ?");
    $check->bind_param("s", $insIc);
    $check->execute();
    $check->bind_result($exists);
    $check->fetch();
    $check->close();

    if ($exists == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Contractor "' . htmlspecialchars($insIc) . '" does not exist.']);
        exit;
    }

    //Fetch current building data
    $select = $conn->prepare("SELECT `name`, `manager`, `ic`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday` FROM buildings WHERE id = ?");
    $select->bind_param("s", $editId);
    $select->execute();
    $select->bind_result($curName, $curManager, $curIc, $curMonday, $curTuesday, $curWednesday, $curThursday, $curFriday, $curSaturday, $curSunday);
    if ($select->fetch()) {
        //Compare current values with submitted ones
        if (
            $curName === $insName &&
            $curManager === $insManager &&
            $curIc === $insIc &&
            (bool)$curMonday === $monday &&
            (bool)$curTuesday === $tuesday &&
            (bool)$curWednesday === $wednesday &&
            (bool)$curThursday === $thursday &&
            (bool)$curFriday === $friday &&
            (bool)$curSaturday === $saturday &&
            (bool)$curSunday === $sunday
        ) {
            echo json_encode(['status' => 'info', 'message' => 'No changes were made.']);
            $select->close();
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Building not found.']);
        $select->close();
        exit;
    }
    $select->close();

    //confirm insertions
    if($insName === "") {
        echo json_encode(['status' => 'error', 'message' => 'Building Name cannot be empty.']);
        exit;
    } else if(strlen($insName) > $maxLength) {
        echo json_encode(['status' => 'error', 'message' => "Building Name cannot exceed $maxLength characters."]);
        exit;
    } else if($insManager === "---" || $insManager === "") {
        echo json_encode(['status' => 'error', 'message' => 'Manager cannot be empty.']);
        exit;
    } else if($insIc === "") {
        echo json_encode(['status' => 'error', 'message' => 'Contractor cannot be empty.']);
        exit;
    } else if($monday === false && $tuesday === false && $wednesday === false &&  $thursday === false && $friday === false && $saturday === false && $sunday === false) {
        echo json_encode(['status' => 'error', 'message' => 'No days are selected.']);
        exit;
    } else {
        $ins = $conn->prepare("UPDATE buildings SET `name` = ?, `manager` = ?, `ic` = ?, `monday` = ?, `tuesday` = ?, `wednesday` = ?, `thursday` = ?, `friday` = ?, `saturday` = ?, `sunday` = ? WHERE id = ?");
        $ins->bind_param("sssiiiiiiis", $insName, $insManager, $insIc, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday, $sunday, $editId);
        try {
            $ins->execute();
            echo json_encode(['status' => 'success', 'message' => 'Building updated successfully!']);
            exit;
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            echo json_encode(['status' => 'success', 'message' => "Insertion failed. $errorMessage"]);
            exit;
        }
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
$conn->close();
?>
