<?php
// [fill in the KPI's]
try {
    // Count total roles
    $Stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM roles');
    $Stmt->execute();
    $getTotalRoles = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log($e->getMessage());
    userLog($pdo, "Read", "System", "Failed to count total roles", "Failure");
    dashboardFeedback('Failed to count total roles. Please try again later.');
    exit;
}

try {
    // Count total admins
    $Stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM users');
    $Stmt->execute();
    $getTotalUsers = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log('Failed to count total users: ' . $e->getMessage());
    dashboardFeedback($pdo, "Read", "System", "Failed to count total users", "Failure");
    exit;
}

try {
    // Count total active roles
    $Stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM roles WHERE status = "Active"');
    $Stmt->execute();
    $getTotalActiveRoles = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log($e->getMessage());
    userLog($pdo, "Read", "System", "Failed to count total active roles", "Failure");
    dashboardFeedback('Failed to count total active roles. Please try again later.');
    exit;
}

try {
    // Count total inactive roles
    $Stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM roles WHERE status = "inactive"');
    $Stmt->execute();
    $getTotalInactiveRoles = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log($e->getMessage());
    userLog($pdo, "Read", "System", "Failed to count total inactive roles", "Failure");
    dashboardFeedback('Failed to count total inactive roles. Please try again later.');
    exit;
}

try {
    // Count total permissions
    $Stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM role_permissions');
    $Stmt->execute();
    $getTotalPermissions = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log($e->getMessage());
    userLog($pdo, "Read", "System", "Failed to count total role permissions", "Failure");
    dashboardFeedback('Failed to count total role permissions. Please try again later.');
    exit;
}

try {
    // show roles table and how many users are assigned to each role
    $Stmt = $pdo->prepare('SELECT r.id, r.name, r.description, r.status, r.created_at, COUNT(u.id) AS users FROM roles r
                            LEFT JOIN users u ON r.id = u.id
                            GROUP BY r.id;');
    $Stmt->execute();
    $getRoles = $Stmt->fetchAll();

} catch(PDOException $e) {
    error_log($e->getMessage());
    userLog($pdo, "Read", "System", "Failed to show all roles", "Failure");
    dashboardFeedback('Failed to show roles. Please try again later.');
    exit;
}

?>