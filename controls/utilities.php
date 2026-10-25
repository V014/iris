<?php
// Log the user activity usually login and logout with minor errors
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
        error_log($e->getMessage());
        alert("Failed to log error: " . $e->getMessage());
    }
}

// Log the users who make changes and where
function userChanges($pdo, $action, $target, $target_id, $details, $status) {
    try {
        $insertStmt = $pdo->prepare('
            INSERT INTO user_logs (user_id, action, target, target_id, details, status, created_at)
            VALUES (:user_id, :action, :target, :target_id, :details, :status, NOW())
        ');
        $insertStmt->execute([
            'user_id' => $_SESSION['user_id'],
            'action' => $action,
            'target' => $target,
            'target_id' => $target_id,
            'details' => $details,
            'status' => $status,
        ]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        alert("Failed to log changes: " . $e->getMessage());
    }
}
?>