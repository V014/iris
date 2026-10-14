<?php 
// Include utilities page
include_once '../controls/utilities.php';

// create function that handles user feedback
function dashboardFeedback(?PDO $pdo = null, $action, $target, $details, $status): never {
    if ($pdo) {
        userLog($pdo, $action, $target, $details, $status);
    }
    echo '<p>' . htmlspecialchars($details, ENT_QUOTES, 'UTF-8') . '</p>';
    exit;
}

// [fill in the KPI's]
try {
    // Count total admins
    $Stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM users WHERE role = "Admin"');
    $Stmt->execute();
    $getTotalAdmins = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log('Failed to count total admins: ' . $e->getMessage());
    dashboardFeedback($pdo, "Read", "System", "Failed to count total admins", "Failure");
    exit;
}

try {
    // Count inactive admins
    $Stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM users WHERE role = "Admin" AND status = "Inactive"');
    $Stmt->execute();
    $getTotalInactiveAdmins = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log('Failed to count inactive admins: ' . $e->getMessage());
    dashboardFeedback($pdo, "Read", "System", "Failed to count inactive admins", "Failure");
    exit;
}

try {
    // Count active admins
    $Stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM users WHERE role = "Admin" AND status = "Active"');
    $Stmt->execute();
    $getTotalActiveAdmins = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log('Failed to count active admins: ' . $e->getMessage());
    dashboardFeedback($pdo, "Read", "System", "Failed to count active admins", "Failure");
    exit;
}

try {
    // Count accountants
    $Stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM users WHERE role = "Accountant"');
    $Stmt->execute();
    $getTotalAccountants = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log('Failed to count accountants: ' . $e->getMessage());
    dashboardFeedback($pdo, "Read", "System", "Failed to count accountants", "Failure");
    exit;
}

try {
    // Count operators
    $Stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM users WHERE role = "Operator"');
    $Stmt->execute();
    $getTotalActiveOperators = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log('Failed to count operators: ' . $e->getMessage());
    dashboardFeedback($pdo, "Read", "System", "Failed to count operators", "Failure");
    exit;
}

try {
    // Count all managers
    $Stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM users WHERE role = "Admin"');
    $Stmt->execute();
    $getTotalManagers = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log('Failed to count managers: ' . $e->getMessage());
    dashboardFeedback($pdo, "Read", "System", "Failed to count managers", "Failure");
    exit;
}

try {
    // Count admin activity in last 7 days
    $Stmt = $pdo->prepare('SELECT COUNT(user_id) FROM user_logs WHERE user_id = :user_id AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
    $Stmt->bindParam(':user_id', $_SESSION['user_id']);
    $Stmt->execute();
    $getActivityIn7Days = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log('Failed to count admin activity in last 7 days: ' . $e->getMessage());
    dashboardFeedback($pdo, "Read", "System", "Failed to count admin activity in last 7 days", "Failure");
    exit;
}

try {
    // Count admin activity in last 30 days
    $Stmt = $pdo->prepare('SELECT COUNT(user_id) FROM user_logs WHERE user_id = :user_id AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
    $Stmt->bindParam(':user_id', $_SESSION['user_id']);
    $Stmt->execute();
    $getActivityIn30Days = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log('Failed to count admin activity in last 30 days: ' . $e->getMessage());
    dashboardFeedback($pdo, "Read", "System", "Failed to count admin activity in last 30 days", "Failure");
    exit;
}

try {
    // show last login of admin
    $Stmt = $pdo->prepare('SELECT MAX(created_at) FROM user_logs WHERE user_id = :user_id');
    $Stmt->bindParam(':user_id', $_SESSION['user_id']);
    $Stmt->execute();
    $getLastLogin = $Stmt->fetch();
    
}   catch(PDOException $e){
    error_log('Failed to count last login of admin: ' . $e->getMessage());
    dashboardFeedback($pdo, "Read", "System", "Failed to count last login of admin", "Failure");
    exit;
}

// read table data
try {
    // show user and user_log table
    $Stmt = $pdo->prepare('SELECT u.id, u.username, u.role, u.status, u.contact, 
                            MAX(ul.created_at) AS last_login, 
                            (u.2FA_secret IS NOT NULL) AS has_2fa 
                            FROM users u 
                            LEFT JOIN user_logs ul ON u.id = ul.user_id
                            GROUP BY u.id
                            ORDER BY last_login DESC
                            LIMIT 10;');
    $Stmt->execute();
    $getUsers = $Stmt->fetchAll();

} catch(PDOException $e) {
    error_log('Failed to show all users: ' . $e->getMessage());
    dashboardFeedback($pdo, "Read", "System", "Failed to show all users", "Failure");
    exit;
}
?>