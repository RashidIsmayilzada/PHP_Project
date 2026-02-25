<?php
declare(strict_types=1);

namespace App\Framework;

use App\Config;
use PDO;
use PDOException;

abstract class Repository
{
    protected PDO $db;

    public function __construct()
    {
        // Create the database connection when the repository is constructed.
        $this->db = $this->connect();
    }

    private function connect(): PDO
    {
        // Build the DSN string used to connect to MySQL/MariaDB.
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            Config::dbHost(), // Assumes Config::dbHost() will return 'mysql'
            Config::dbName() // Assumes Config::dbName() will return 'developmentdb'
        );

        try {
            // Connect with PDO and enable exceptions for errors.
            return new PDO(
                $dsn,
                Config::dbUser(), // Assumes Config::dbUser() will return 'root' or 'developer'
                Config::dbPass(), // Assumes Config::dbPass() will return 'secret123'
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            // Fail fast with a 500 response if the connection fails.
            http_response_code(500);
            error_log('Database connection failed: ' . $e->getMessage()); // Log the error
            echo 'An internal server error occurred. Please try again later.'; // Generic user message
            exit;
        }
    }

    // This static method from the example seems redundant if all repositories extend this class
    // and use the protected $this->db. I will keep the protected connect method.
    // However, the provided example included a static getPDO, so I'll include it for now.
    // It uses getenv, which is less ideal than Config methods. Will use Config methods here.
    public static function getPDO(): PDO
    {
        $host = Config::dbHost();
        $db   = Config::dbName();
        $user = Config::dbUser();
        $pass = Config::dbPass();

        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        // Run a query and return all rows.
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    protected function fetch(string $sql, array $params = []): ?array
    {
        // Run a query and return a single row (or null).
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    protected function execute(string $sql, array $params = []): bool
    {
        // Run a query that doesn't need results (insert/update/delete).
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    protected function lastInsertId(): int
    {
        // Return the last auto-increment ID from this connection.
        return (int)$this->db->lastInsertId();
    }
}
