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
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'User';
    $confirm = trim($_POST['confirm_password'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $twoFA = trim($_POST['twoFA'] ?? '');
    $status = $_POST['status'] ?? 'Active';

    // check to see if all required fields are filled
    if ($username === '' || $password === '' || $confirm === '' || $role === '' || $contact === '') {
    dashboardFeedback('All fields are required.');
    }
     // check if the password is greater than 8 characters
    if (strlen($password) < 8) {
    dashboardFeedback('Password must be at least 8 characters long.');
    }

    // check if passwords don't match
    if ($password !== $confirm) {
        dashboardFeedback('Passwords do not match.');
    }

    // check if 2FA secret is similar to password
    if ($password === $twoFA) {
        dashboardFeedback('Two-Factor authentication secret cannot be similar to password');
    }

} catch(PDOException $e){
    error_log($e->getMessage());
    userLog($pdo, "Create", "System", "Failed to create user", "Failure");
    dashboardFeedback('Failed to create user. Please try again later.');
    exit;
}
?>