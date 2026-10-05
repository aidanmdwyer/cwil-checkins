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

    $deleteAuditStmt1 = $conn->prepare("DELETE FROM closet_audits WHERE auditId = ?;");
    $deleteAuditStmt2 = $conn->prepare("DELETE FROM closet_audit_items WHERE auditId = ?;");
    $deleteAuditStmt3 = $conn->prepare("DELETE FROM closet_audit_comments WHERE auditId = ?;");

    $deleteAuditStmt1->bind_param("i", $auditId);
    $deleteAuditStmt2->bind_param("i", $auditId);
    $deleteAuditStmt3->bind_param("i", $auditId);
    $deleteAuditStmt1->execute();
    $deleteAuditStmt2->execute();
    $deleteAuditStmt3->execute();

    if($deleteAuditStmt1->affected_rows > 0 && $deleteAuditStmt2->affected_rows > 0) {
        header("Location: /php/closetAudits.php?deleted=" . urlencode($buildingName));
        exit();
    } else {
        $errorMsg = "Failed to delete audit.";
        header("Location: /php/closetAudits.php?error=" . urlencode($errorMsg));
        exit();
    }

} else {
    $errorMsg = "Invalid request.";
    header("Location: /php/closetAudits.php?error=" . urlencode($errorMsg));
    exit();
}
?>