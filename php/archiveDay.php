<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

$now = new DateTime('now', new DateTimeZone('America/Chicago'));

//cpanel doesn't let me change cron time zone, this is a workaround, executing every hour
if($now->format('H') === '03') {
    $TO = "aidandyr@gmail.com, nfreeman@gocitywide.com";
    $SUBJECT = "Archive Report, CWIL Check-Ins";
    $BODY = "<html><body><b>Archive Attempt:<br>" . $now->format("M d, Y") . "<br>" . ltrim($now->format("h:i A"), "0") . "</b><br><br>";
    $HEADERS = "MIME-Version: 1.0\r\n";
    $HEADERS .= "Content-type: text/html; charset=UTF-8\r\n";

    require_once 'db.php';

    if ($conn->connect_error) {
        $BODY .= "<span style='color: red'>Connection failed: " . $conn->connect_error . "</span>";
        $BODY .= "</body></html>";
        $SUBJECT = "ERROR: Archive Report, CWIL Check-Ins";
        mail($TO, $SUBJECT, $BODY, $HEADERS);
        exit();
    }

    $now->modify('-1 day');
    $archiveDate = $now->format('Y-m-d');
    $dayOfWeek = strtolower($now->format('l'));

    $stmt = $conn->prepare("
INSERT INTO archive (archiveDate, name, manager, ic, checked, checkedTime)
SELECT ?, name, manager, ic, checked, checkedTime
FROM buildings WHERE $dayOfWeek = 1 AND active = 1
");
    if (!$stmt) {
        $BODY .= "<span style='color: red'>Prepare failed: " . $conn->error . "</span>";
        $BODY .= "</body></html>";
        $SUBJECT = "ERROR: Archive Report, CWIL Check-Ins";
        mail($TO, $SUBJECT, $BODY, $HEADERS);
        exit();
    }

    $stmt->bind_param("s", $archiveDate);
    $duplicate = $conn->query("SELECT 1 FROM archive WHERE archiveDate = '$archiveDate' LIMIT 1;");
    if ($duplicate && $duplicate->num_rows > 0) {
        $BODY .= "<span style='color: red'>Existing archive found for $archiveDate. Overwriting...</span><br><br>";
        $SUBJECT = "WARNING: Archive Report, CWIL Check-Ins";
        $conn->query("DELETE FROM archive WHERE archiveDate = '$archiveDate';");
    }

    if (!$stmt->execute()) {
        $BODY .= "<span style='color: red'>Execute failed: " . $stmt->error . "</span>";
        $BODY .= "</body></html>";
        $SUBJECT = "ERROR: Archive Report, CWIL Check-Ins";
        mail($TO, $SUBJECT, $BODY, $HEADERS);
        exit();
    } else {
        if (!$conn->query("UPDATE buildings SET checked = 0, checkedTime = NULL")) {
            $BODY .= "<span style='color: red'>Execute failed: " . $conn->error . "</span>";
            $BODY .= "</body></html>";
            $SUBJECT = "ERROR: Archive Report, CWIL Check-Ins";
            mail($TO, $SUBJECT, $BODY, $HEADERS);
            exit();
        }
    }
    if (!$conn->query("UPDATE sse_signal SET version = 0 WHERE id = 1")) {
        $BODY .= "<span style='color: red'>SSE reset failed: " . $conn->error . "</span><br><br>";
    }

    $BODY .= "Archived " . $stmt->affected_rows . " rows for date $archiveDate.";
    $BODY .= "</body></html>";

    echo "Archived " . $stmt->affected_rows . " rows for date $archiveDate.";
    echo "<br><br>";

    if (mail($TO, $SUBJECT, $BODY, $HEADERS)) {
        echo "Email sent";
    } else {
        echo "Email failed";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Wrong time";
}
?>