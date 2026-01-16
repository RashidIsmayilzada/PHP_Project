<?php

namespace App\Enums;

// User role enumeration for role-based access control
enum UserRole: string
{
    case STUDENT = 'student';
    case TEACHER = 'teacher';

    // Check if role is student
    public function isStudent(): bool
    {
        return $this === self::STUDENT;
    }

    // Check if role is teacher
    public function isTeacher(): bool
    {
        return $this === self::TEACHER;
    }

    // Get all available roles as array
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    // Create role from string value with validation
    public static function tryFromString(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }
}
