<?php
namespace App\Database;

use App\Config;
use PDO;
use PDOException;

// Database connection class
class Database
{
    // @var PDO|null
    private static $connection = null;

    // Get a singleton PDO connection
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $dsn = "mysql:host=" . Config::getDbServerName() . ";dbname=" . Config::getDbName() . ";charset=utf8mb4";
                self::$connection = new PDO(
                    $dsn,
                    Config::getDbUsername(),
                    Config::getDbPassword(),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                throw new PDOException("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }

    // Test database connection
    public static function testConnection(): bool
    {
        try {
            $pdo = self::getConnection();
            $pdo->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
