<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

interface GradePolicyInterface
{
    public function letterForPercentage(float $percentage): string;
    public function gpaForPercentage(float $percentage): float;
    public function getDefaultCourseCredits(): float;
}
