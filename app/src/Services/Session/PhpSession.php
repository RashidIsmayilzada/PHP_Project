<?php

declare(strict_types=1);

namespace App\Services\Session;

use App\Services\Interfaces\SessionInterface;

final class PhpSession implements SessionInterface
{
    public function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public function start(): void
    {
        session_start();
    }

    public function ensureStarted(): void
    {
        if (!$this->isActive()) {
            $this->start();
        }
    }
}
