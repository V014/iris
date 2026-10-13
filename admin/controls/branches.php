<?php
// [fill in the KPI's]
try {
    // Count total branches
    $Stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM branches');
    $Stmt->execute();
    $getTotalBranches = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log($e->getMessage());
    userLog($pdo, "Read", "System", "Failed to count total branches", "Failure");
    dashboardFeedback('Failed to count total branches. Please try again later.');
    exit;
}

try {
    // Count total active branches
    $Stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM branches WHERE status = "Active"');
    $Stmt->execute();
    $getTotalActiveBranches = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log($e->getMessage());
    userLog($pdo, "Read", "System", "Failed to count total active branches", "Failure");
    dashboardFeedback('Failed to count total active branches. Please try again later.');
    exit;
}

try {
    // Count total inactive branches
    $Stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM branches WHERE status = "inactive"');
    $Stmt->execute();
    $getTotalInactiveBranches = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log($e->getMessage());
    userLog($pdo, "Read", "System", "Failed to count total inactive branches", "Failure");
    dashboardFeedback('Failed to count total inactive branches. Please try again later.');
    exit;
}

try {
    // show branches table
    $Stmt = $pdo->prepare('SELECT id, name, description, location, status, created_at, updated_at FROM branches;');
    $Stmt->execute();
    $getBranches = $Stmt->fetchAll();

} catch(PDOException $e) {
    error_log($e->getMessage());
    userLog($pdo, "Read", "System", "Failed to show all branches", "Failure");
    dashboardFeedback('Failed to show branches. Please try again later.');
    exit;
}
?>