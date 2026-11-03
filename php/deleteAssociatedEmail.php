<?php
include 'accountProperties.php';

if (!accountProperties('Managers Page') && !accountProperties('Contractors Page')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to perform this action.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['accountName']) && isset($_POST['accountType']) && isset($_POST['email'])) {
        $accountName = $_POST['accountName'];
        $accountType = $_POST['accountType'];
        $email = $_POST['email'];

        require_once 'db.php';

        $stmt = null;

        try {
            $stmt = $conn->prepare("DELETE FROM `associated_emails` WHERE accountName = ? AND accountType = ? AND email = ?;");
            $stmt->bind_param("sss", $accountName, $accountType, $email);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Email successfully deleted.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to delete email.'
                ]);
            }
        } finally {
            if ($stmt !== null) {
                $stmt->close();
            }

            $conn->close();
        }
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing required parameters.'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>