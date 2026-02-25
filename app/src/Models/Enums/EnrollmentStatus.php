<?php

namespace App\Enums;

// Enrollment status enumeration for tracking student course enrollments
enum EnrollmentStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case COMPLETED = 'completed';
    case DROPPED = 'dropped';

    // Check if enrollment is currently active
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    // Check if enrollment has ended (completed or dropped)
    public function hasEnded(): bool
    {
        return $this === self::COMPLETED || $this === self::DROPPED;
    }

    // Get all available statuses as array
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    // Create status from string value with validation
    public static function tryFromString(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }
}
