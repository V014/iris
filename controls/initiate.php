<?php
// returns feedback to user upon error
function initiateFeedback(string $message): never
{
    echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    exit;
}

// makes sure request method is post
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // create the database first
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $server = new mysqli($host, $user, $pass);
    $server->set_charset('utf8mb4');
    $server->query('CREATE DATABASE IF NOT EXISTS `iris` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    $server->close();

    $database = new mysqli($host, $user, $pass, 'iris');
    $database->set_charset('utf8mb4');

    $tableCheck = $database->query("SHOW TABLES LIKE 'users'");
    // look for the schema
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

        // create the trigger
        $triggerSql = <<<SQL
            CREATE TRIGGER trg_daily_log_update
            AFTER UPDATE ON daily_branch_log
            FOR EACH ROW
            BEGIN
            IF OLD.milling_revenue  <> NEW.milling_revenue
            OR OLD.shelling_revenue <> NEW.shelling_revenue
            OR OLD.opening_kwh      <> NEW.opening_kwh
            OR OLD.closing_kwh      <> NEW.closing_kwh
            OR OLD.is_void          <> NEW.is_void THEN
                INSERT INTO user_logs (user_id, action, target, target_id, details, status)
                VALUES (
                NEW.user_id, 'Update', 'Daily log', NEW.id,
                CONCAT('old: milling_revenue=', OLD.milling_revenue,
                        ', shelling_revenue=', OLD.shelling_revenue,
                        ', opening_kwh=', OLD.opening_kwh,
                        ', closing_kwh=', OLD.closing_kwh,
                        ', is_void=', OLD.is_void,
                        ' | new: milling_revenue=', NEW.milling_revenue,
                        ', shelling_revenue=', NEW.shelling_revenue,
                        ', opening_kwh=', NEW.opening_kwh,
                        ', closing_kwh=', NEW.closing_kwh,
                        ', is_void=', NEW.is_void),
                'Success'
                );
            END IF;
            END
        SQL;
        $database->query($triggerSql);
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