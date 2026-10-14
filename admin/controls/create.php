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

// check for valid request method to server
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

try{
    // create user

} catch(PDOException $e){
    error_log($e->getMessage());
    userLog($pdo, "Create", "System", "Failed to create user", "Failure");
    dashboardFeedback('Failed to create user. Please try again later.');
    exit;
}
?>