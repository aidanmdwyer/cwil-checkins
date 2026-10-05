<?php
include 'accountProperties.php';

if (!accountProperties('Closet Audits Page')) {
    http_response_code(403);
    die('Forbidden: You do not have permission to perform this action.');
}

if (isset($_GET['auditId']) && isset($_GET['buildingName'])) {
    require_once 'db.php';

    $auditId = $_GET['auditId'];
    $buildingName = $_GET['buildingName'];

    $deleteAuditStmt = $conn->prepare("
        DELETE FROM closet_audit_comments WHERE auditId = ?;
        DELETE FROM closet_audit_items WHERE auditId = ?;
        DELETE FROM closet_audits WHERE auditId = ?;
    ");

    $deleteAuditStmt->bind_param("iii", $auditId, $auditId, $auditId);
    $deleteAuditStmt->execute();

    if($deleteAuditStmt->affected_rows > 0) {
        header("Location: /php/deleteAudit.php?deleted=" . urlencode($buildingName));
        exit();
    } else {
        $errorMsg = "Failed to delete audit.";
        header("Location: /php/deleteAudit.php?error=" . urlencode($errorMsg));
        exit();
    }

} else {
    $errorMsg = "Invalid request.";
    header("Location: /php/deleteAudit.php?error=" . urlencode($errorMsg));
    exit();
}
?>