<?php
namespace App\Utils;

class PasswordHelper
{
    /**
     * Hash a password using bcrypt
     *
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify a password against a hash
     *
     * @param string $password Plain text password to verify
     * @param string $hash Hashed password from database
     * @return bool True if password matches, false otherwise
     */
    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if a password hash needs to be rehashed
     * Useful for upgrading password hashing algorithm
     *
     * @param string $hash Hashed password from database
     * @return bool True if needs rehashing, false otherwise
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
}
