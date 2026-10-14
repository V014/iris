<?php
// Get name of browser
// Log the user activity
function userLog($pdo, $action, $target, $details, $status) {
    try {
        $insertStmt = $pdo->prepare('
            INSERT INTO user_logs (user_id, action, target, details, status, created_at)
            VALUES (:user_id, :action, :target, :details, :status, NOW())
        ');
        $insertStmt->execute([
            'user_id' => $_SESSION['user_id'],
            'action' => $action,
            'target' => $target,
            'details' => $details,
            'status' => $status,
        ]);
    } catch (PDOException $e) {
        alert("Failed to log error: " . $e->getMessage());
    }
}
?>