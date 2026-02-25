<?php
namespace App;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;

class Config
{
    private static bool $initialized = false;

    // Load environment variables from .env file
    private static function initialize(): void
    {
        if (!self::$initialized) {
            try {
                $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
                $dotenv->load();
            } catch (InvalidPathException $e) {
                // .env file not found, but that's okay, we have defaults.
                // You could log this event in a real application.
            } finally {
                self::$initialized = true;
            }
        }
    }

    // Get database server name from environment
    public static function getDbServerName(): string
    {
        self::initialize();
        return $_ENV['DB_SERVER_NAME'] ?? 'mysql';
    }

    // Get database username from environment
    public static function getDbUsername(): string
    {
        self::initialize();
        return $_ENV['DB_USERNAME'] ?? 'root';
    }

    // Get database password from environment
    public static function getDbPassword(): string
    {
        self::initialize();
        return $_ENV['DB_PASSWORD'] ?? '';
    }

    // Get database name from environment
    public static function getDbName(): string
    {
        self::initialize();
        return $_ENV['DB_NAME'] ?? 'developmentdb';
    }

    // Get application environment (development, production, etc)
    public static function getAppEnv(): string
    {
        self::initialize();
        return $_ENV['APP_ENV'] ?? 'production';
    }

    // Check if application is in debug mode
    public static function isDebug(): bool
    {
        self::initialize();
        return filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    // Get application base URL
    public static function getAppUrl(): string
    {
        self::initialize();
        return $_ENV['APP_URL'] ?? 'http://localhost';
    }

    // Get session lifetime in seconds
    public static function getSessionLifetime(): int
    {
        self::initialize();
        return (int)($_ENV['SESSION_LIFETIME'] ?? 3600);
    }

    // Get minimum password length requirement
    public static function getPasswordMinLength(): int
    {
        self::initialize();
        return (int)($_ENV['PASSWORD_MIN_LENGTH'] ?? 8);
    }

    // Backwards compatibility constants - deprecated, use methods instead
    public const DB_SERVER_NAME = 'mysql';
    public const DB_USERNAME = 'root';
    public const DB_PASSWORD = 'secret123';
    public const DB_NAME = 'developmentdb';
}
