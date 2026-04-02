<?php
declare(strict_types=1);

namespace App\Repositories\Data;

final class CourseGradeData
{
    public function __construct(
        private readonly float $pointsEarned,
        private readonly float $maxPoints
    ) {
    }

    public function getPointsEarned(): float
    {
        return $this->pointsEarned;
    }

    public function getMaxPoints(): float
    {
        return $this->maxPoints;
    }
}
