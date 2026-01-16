<?php
namespace App\Utils;

class PasswordHelper
{
    // Hash a password using bcrypt for secure storage
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Check if a plain text password matches the hashed version
    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    // See if an old password hash should be upgraded to a newer algorithm
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
}
