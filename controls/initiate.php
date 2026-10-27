<?php
function initiateFeedback(string $message): never
{
    echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$host = 'localhost';
$user = 'root';
$pass = '';

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $server = new mysqli($host, $user, $pass);
    $server->set_charset('utf8mb4');
    $server->query('CREATE DATABASE IF NOT EXISTS `iris` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    $server->close();

    $database = new mysqli($host, $user, $pass, 'iris');
    $database->set_charset('utf8mb4');

    $tableCheck = $database->query("SHOW TABLES LIKE 'users'");
    if ($tableCheck->num_rows === 0) {
        $schemaPath = dirname(__DIR__) . '/database/iris.sql';
        $schema = file_get_contents($schemaPath);
        if ($schema === false) {
            initiateFeedback('Could not read the database schema.');
        }

        $database->multi_query($schema);
        do {
            if ($result = $database->store_result()) {
                $result->free();
            }
        } while ($database->more_results() && $database->next_result());

        $database->query('DELETE FROM `user_logs`');
        $database->query('DELETE FROM `users`');
        $database->query("ALTER TABLE `users` MODIFY `contact` varchar(10) NOT NULL DEFAULT ''");
        $database->query('ALTER TABLE `users` AUTO_INCREMENT = 1');
    }

    $userCount = $database->query('SELECT COUNT(*) AS total FROM `users`')->fetch_assoc()['total'];
    $database->close();

    $destination = (int) $userCount > 0 ? 'index.php' : 'setup.php';
    if (!empty($_SERVER['HTTP_HX_REQUEST'])) {
        header('HX-Redirect: ' . $destination);
    } else {
        header('Location: ../' . $destination, true, 303);
    }
    exit;
} catch (Throwable $e) {
    error_log($e->getMessage());
    initiateFeedback('Database initialization failed. Check your database settings and try again.');
}