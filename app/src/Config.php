<?php
namespace App;

use Dotenv\Dotenv;

class Config
{
    private static bool $initialized = false;

    // Load environment variables from .env file
    private static function initialize(): void
    {
        if (!self::$initialized) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
            self::$initialized = true;
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

    // Get grade A threshold percentage
    public static function getGradeAThreshold(): float
    {
        self::initialize();
        return (float)($_ENV['GRADE_A_THRESHOLD'] ?? 90);
    }

    // Get grade B threshold percentage
    public static function getGradeBThreshold(): float
    {
        self::initialize();
        return (float)($_ENV['GRADE_B_THRESHOLD'] ?? 80);
    }

    // Get grade C threshold percentage
    public static function getGradeCThreshold(): float
    {
        self::initialize();
        return (float)($_ENV['GRADE_C_THRESHOLD'] ?? 70);
    }

    // Get grade D threshold percentage
    public static function getGradeDThreshold(): float
    {
        self::initialize();
        return (float)($_ENV['GRADE_D_THRESHOLD'] ?? 60);
    }

    // Get GPA value for grade A
    public static function getGpaA(): float
    {
        self::initialize();
        return (float)($_ENV['GPA_A'] ?? 4.0);
    }

    // Get GPA value for grade B
    public static function getGpaB(): float
    {
        self::initialize();
        return (float)($_ENV['GPA_B'] ?? 3.0);
    }

    // Get GPA value for grade C
    public static function getGpaC(): float
    {
        self::initialize();
        return (float)($_ENV['GPA_C'] ?? 2.0);
    }

    // Get GPA value for grade D
    public static function getGpaD(): float
    {
        self::initialize();
        return (float)($_ENV['GPA_D'] ?? 1.0);
    }

    // Get GPA value for grade F
    public static function getGpaF(): float
    {
        self::initialize();
        return (float)($_ENV['GPA_F'] ?? 0.0);
    }

    // Get default course credits when not specified
    public static function getDefaultCourseCredits(): float
    {
        self::initialize();
        return (float)($_ENV['DEFAULT_COURSE_CREDITS'] ?? 3.0);
    }

    // Backwards compatibility constants - deprecated, use methods instead
    public const DB_SERVER_NAME = 'mysql';
    public const DB_USERNAME = 'root';
    public const DB_PASSWORD = 'secret123';
    public const DB_NAME = 'developmentdb';
}
