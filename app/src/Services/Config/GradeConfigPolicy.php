<?php

declare(strict_types=1);

namespace App\Services\Config;

use App\Constants\GradeConfig;
use App\Services\Interfaces\GradePolicyInterface;

final class GradeConfigPolicy implements GradePolicyInterface
{
    public function letterForPercentage(float $percentage): string
    {
        if ($percentage >= GradeConfig::GRADE_A_THRESHOLD) {
            return 'A';
        }

        if ($percentage >= GradeConfig::GRADE_B_THRESHOLD) {
            return 'B';
        }

        if ($percentage >= GradeConfig::GRADE_C_THRESHOLD) {
            return 'C';
        }

        if ($percentage >= GradeConfig::GRADE_D_THRESHOLD) {
            return 'D';
        }

        return 'F';
    }

    public function gpaForPercentage(float $percentage): float
    {
        if ($percentage >= GradeConfig::GRADE_A_THRESHOLD) {
            return GradeConfig::GPA_A;
        }

        if ($percentage >= GradeConfig::GRADE_B_THRESHOLD) {
            return GradeConfig::GPA_B;
        }

        if ($percentage >= GradeConfig::GRADE_C_THRESHOLD) {
            return GradeConfig::GPA_C;
        }

        if ($percentage >= GradeConfig::GRADE_D_THRESHOLD) {
            return GradeConfig::GPA_D;
        }

        return GradeConfig::GPA_F;
    }

    public function getDefaultCourseCredits(): float
    {
        return GradeConfig::DEFAULT_COURSE_CREDITS;
    }
}
