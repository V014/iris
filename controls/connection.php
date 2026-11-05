<?php
    // db.php - Database Connection Configuration

    // 1. Database Configuration Parameters
    $host    = 'localhost';
    $db      = 'iris';
    $user    = 'root';
    $pass    = '';
    $charset = 'utf8mb4';

    // 2. Define Data Source Name (DSN)
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    // 3. Configure PDO Options
    $options = [
        // Throw PDOExceptions on errors for better error handling
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Fetch associative arrays by default ($row['column_name'])
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Disable emulation to use true prepared statements (prevents SQL injection)
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // 4. Instantiate PDO Connection inside a Try-Catch Block
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        // Log the error internally and hide sensitive database details from the user
        error_log($e->getMessage());
        header("Location: initiate.php");
        exit();
    }
?>