<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

interface SessionInterface
{
    public function isActive(): bool;
    public function start(): void;
    public function ensureStarted(): void;
}
