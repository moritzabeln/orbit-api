<?php
// db.php

function getPDO(): PDO {
    static $pdo = null; // ensures we only create one PDO instance

    if ($pdo === null) {
        $config = require __DIR__ . '/config.php';
        
        $host    = $config['DB_HOST'];
        $db      = $config['DB_NAME'];
        $user    = $config['DB_USER'];
        $pw      = $config['DB_PASS'];
        $charset = $config['charset'] ?? 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pw, $options);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Database connection failed: ' . $e->getMessage(),
                'details' => $e->getMessage()
            ]);
            exit;
        }
    }

    return $pdo;
}
?>
