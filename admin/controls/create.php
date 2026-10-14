<?php
session_start();
require_once '../../controls/connection.php';
require_once '../../controls/utilities.php';

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id) {
    header('HX-Redirect: ../../index.php');
    exit;
}

// Log validation failures too, if you want an audit trail
function dashboardFeedback(string $message): never {
    if ($pdo) {
        userLog($user_id, 'Create', 'User account', $message . 'Failure');
    }
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
$role = $_POST['role'] ?? '';
$confirm = trim($_POST['confirmPassword'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$twoFA = trim($_POST['twoFA'] ?? '');
$status = $_POST['status'] ?? '';

// check to see if all required fields are filled
if ($username === '' || $password === '' || $confirm === '' || $role === '' || $contact === '') {
    dashboardFeedback('All fields are required.');
    exit;
}
    // check if the password is greater than 8 characters
if (strlen($password) < 8) {
    dashboardFeedback('Password must be at least 8 characters long.');
    exit;
}

// check if passwords don't match
if ($password !== $confirm) {
    dashboardFeedback('Passwords do not match.');
    exit;
}

// check if 2FA secret is similar to password
if ($twoFA !== '' && $password === $twoFA) {
    dashboardFeedback('2FA secret cannot be the same as the password.');
}

try{
    // check if the username already exists for the given role
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username AND role = :role LIMIT 1');
    $stmt->execute(['username' => $username, 'role' => $role]);

    if ($stmt->fetch()) {
        dashboardFeedback('Username already registered.');
        exit;
    }

    // encrypt the main password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Store NULL when 2FA is off
    $twoFA_secret = $twoFA === '' ? null : password_hash($twoFA, PASSWORD_DEFAULT);

    $insertStmt = $pdo->prepare('
        INSERT INTO users (username, role, password, 2FA_secret, status, contact, created_at)
        VALUES (:username, :role, :password, :2FA_secret, :status, :contact, NOW())
    ');

    $insertStmt->execute([
        'username' => $username,
        'role' => $role,
        'password' => $passwordHash,
        '2FA_secret' => $twoFA_secret, 
        'status' => $status,
        'contact' => $contact,
    ]);

    // log the user entry
    dashboardFeedback('Successfully created user.');

} catch(PDOException $e){
    error_log('User creation failed: ' . $e->getMessage());
    dashboardFeedback('Failed to create user. Please try again later.');
    exit;
}

header('HX-Redirect: ../manage.php');
exit;
?>