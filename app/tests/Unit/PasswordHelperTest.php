<?php

namespace App\Tests\Unit;

use App\Utils\PasswordHelper;
use PHPUnit\Framework\TestCase;

class PasswordHelperTest extends TestCase
{
    /**
     * Test password hashing
     */
    public function testPasswordHashing(): void
    {
        $password = 'mySecurePassword123';
        $hash = PasswordHelper::hash($password);

        // Hash should not be empty
        $this->assertNotEmpty($hash);

        // Hash should be different from plain password
        $this->assertNotEquals($password, $hash);

        // Hash should start with bcrypt prefix
        $this->assertStringStartsWith('$2y$', $hash);
    }

    /**
     * Test that same password produces different hashes (salt)
     */
    public function testPasswordHashingWithSalt(): void
    {
        $password = 'mySecurePassword123';
        $hash1 = PasswordHelper::hash($password);
        $hash2 = PasswordHelper::hash($password);

        // Different hashes should be generated due to salt
        $this->assertNotEquals($hash1, $hash2);

        // But both should verify correctly
        $this->assertTrue(PasswordHelper::verify($password, $hash1));
        $this->assertTrue(PasswordHelper::verify($password, $hash2));
    }

    /**
     * Test password verification with correct password
     */
    public function testPasswordVerificationSuccess(): void
    {
        $password = 'mySecurePassword123';
        $hash = PasswordHelper::hash($password);

        $this->assertTrue(PasswordHelper::verify($password, $hash));
    }

    /**
     * Test password verification with incorrect password
     */
    public function testPasswordVerificationFailure(): void
    {
        $password = 'mySecurePassword123';
        $wrongPassword = 'wrongPassword456';
        $hash = PasswordHelper::hash($password);

        $this->assertFalse(PasswordHelper::verify($wrongPassword, $hash));
    }

    /**
     * Test password verification is case sensitive
     */
    public function testPasswordVerificationCaseSensitive(): void
    {
        $password = 'MySecurePassword';
        $hash = PasswordHelper::hash($password);

        $this->assertTrue(PasswordHelper::verify('MySecurePassword', $hash));
        $this->assertFalse(PasswordHelper::verify('mysecurepassword', $hash));
        $this->assertFalse(PasswordHelper::verify('MYSECUREPASSWORD', $hash));
    }

    /**
     * Test empty password handling
     */
    public function testEmptyPasswordHandling(): void
    {
        $emptyPassword = '';
        $hash = PasswordHelper::hash($emptyPassword);

        $this->assertNotEmpty($hash);
        $this->assertTrue(PasswordHelper::verify('', $hash));
        $this->assertFalse(PasswordHelper::verify('anyPassword', $hash));
    }

    /**
     * Test special characters in password
     */
    public function testSpecialCharactersInPassword(): void
    {
        $password = 'P@ssw0rd!#$%^&*()_+-=[]{}|;:",.<>?/~`';
        $hash = PasswordHelper::hash($password);

        $this->assertTrue(PasswordHelper::verify($password, $hash));
    }

    /**
     * Test very long password
     */
    public function testLongPassword(): void
    {
        $password = str_repeat('a', 1000);
        $hash = PasswordHelper::hash($password);

        $this->assertTrue(PasswordHelper::verify($password, $hash));
    }

    /**
     * Test needsRehash for fresh hash
     */
    public function testNeedsRehashForFreshHash(): void
    {
        $password = 'mySecurePassword123';
        $hash = PasswordHelper::hash($password);

        // Fresh hash should not need rehashing
        $this->assertFalse(PasswordHelper::needsRehash($hash));
    }

    /**
     * Test needsRehash with bcrypt hash
     */
    public function testNeedsRehashWithBcryptHash(): void
    {
        $password = 'testPassword';
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Bcrypt hash with current algorithm should not need rehashing
        $this->assertFalse(PasswordHelper::needsRehash($hash));
    }
}
