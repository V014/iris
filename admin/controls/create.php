<?php
require_once '../../controls/connection.php';
require_once '../../controls/utilities.php';

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id) {
    header('Location: ../manage.php');
    exit;
}

// create function that handles user feedback
function dashboardFeedback(string $message): never
{
    echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    exit;
}

try{

} catch(PDOException $e){
    error_log($e->getMessage());
    userLog($pdo, "Create", "System", "Failed to create user", "Failure");
    dashboardFeedback('Failed to create user. Please try again later.');
    exit;
}
?>