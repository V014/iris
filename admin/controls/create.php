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

// store variables and run checks
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

try{
    // check if the username already exists for the given role
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username AND role = :role LIMIT 1');
    $stmt->execute(['username' => $username, 'role' => $role]);

    if ($stmt->fetch()) {
        dashboardFeedback('Username already registered.');
    }

    // encrypt the main password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // encrypt the 2FA secret
    $twoFA_secret = password_hash($twoFA, PASSWORD_DEFAULT);

    $insertStmt = $pdo->prepare('
        INSERT INTO users (username, role, password, 2FA_secret, status, contact, created_at)
        VALUES (:username, :role, :password, :2FA_secret, :status, :contact, NOW())
    ');

    $insertStmt->execute([
        'username' => $username,
        'role' => $role,
        'password' => $passwordHash,
        '2FA_secret' => $twoFA_secret, 
        'status' => "Active",
        'contact' => $contact,
    ]);

    // log the user entry
    userLog($pdo,'Create','User account','Successfully created user','Success');

    // route user to correct dashboard
    $dashboard = match ('Admin') {
        'Admin' => '../manage.php',
        default => 'index.php',
    };

} catch(PDOException $e){
    userLog($pdo, "Create", "System", "Failed to create user", "Failure");
    dashboardFeedback('Failed to create user. Please try again later.');
    exit;
}

header('HX-Redirect: ' . $dashboard);
exit;
?>