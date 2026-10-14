<?php
// start session class and connect to the database
session_start();
require_once 'connection.php';
require_once 'utilities.php';

// create function that handles user feedback
function loginFeedback(string $message): never
{
    echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    exit;
}

// check for valid request method to server
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// contain user variables from form
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// double check if data is available in the variables
if ($username === '' || $password === '') {
    loginFeedback('Please fill in both username and password.');
}

try {
    // cross check database to see if queried user is available
    $stmt = $pdo->prepare('SELECT id, username, password, role FROM users WHERE username = :user LIMIT 1');
    $stmt->execute(['user' => $username]);
    $user = $stmt->fetch();

    // if password is incorrect, let the user know
    if (!$user || !password_verify($password, $user['password'])) {
        // log the failed attempt
        userLog($pdo,'Login','System','Failed login, wrong credentials ','Failure');
        loginFeedback('Invalid login credentials.');
        exit;
    }

    // refill sessions of already active
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    // log the successful login
    userLog($pdo,'Login','System','Successful login','Success');

    // route user to correct dashboard
    $dashboard = match ($user['role']) {
        'Admin' => 'admin/dashboard.php',
        'Accountant' => 'accountant/dashboard.php',
        'Operator' => 'employee/dashboard.php',
        default => 'index.php',
    };

} catch (PDOException $e) {
    error_log($e->getMessage());
    // log the user exit
    userLog($pdo, 'Login', 'System', 'Failed login', 'Failure');
    exit;
}

// switch the pages and exit the operation to save memory
header('HX-Redirect: ' . $dashboard);
exit;
?>