<?php
    session_start();
    require_once 'connection.php';
    require_once 'utilities.php';

    // create function that handles user feedback
    function logoutFeedback(string $message): never
    {
        echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
        exit;
    }

    try {
        // log the user exit
        userLog($pdo, 'Logout', 'System', 'User logged out', 'Success');

    } catch (PDOException $e) {
        error_log($e->getMessage());
        // log the user exit
        userLog($pdo, 'Logout', 'System', 'Failed dashboard logout', 'Failure');
        alert('Logout failed. Please try again later.');
        exit;
    }

    session_destroy();
    header('Location: ../index.php');
    exit;
?>