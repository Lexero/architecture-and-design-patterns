<?php

declare(strict_types=1);

namespace App\ServerThread\Sync;

final class ManualResetEvent
{
    private bool $isSignaled = false;
    private bool $locked = false;

    public function signal(): void
    {
        $this->lock();
        try {
            $this->isSignaled = true;
        } finally {
            $this->unlock();
        }
    }

    public function reset(): void
    {
        $this->lock();
        try {
            $this->isSignaled = false;
        } finally {
            $this->unlock();
        }
    }

    public function waitOne(?int $timeoutMs = null): bool
    {
        $startTime = microtime(true);
        $timeoutSec = $timeoutMs !== null ? $timeoutMs / 1000.0 : null;

        while (true) {
            $this->lock();
            try {
                if ($this->isSignaled) {
                    return true;
                }
            } finally {
                $this->unlock();
            }

            if ($timeoutSec !== null) {
                $elapsed = microtime(true) - $startTime;
                if ($elapsed >= $timeoutSec) {
                    return false;
                }
            }

            usleep(100);
        }
    }

    public function isSignaled(): bool
    {
        $this->lock();
        try {
            return $this->isSignaled;
        } finally {
            $this->unlock();
        }
    }

    private function lock(): void
    {
        while ($this->locked) {
            usleep(1);
        }
        $this->locked = true;
    }

    private function unlock(): void
    {
        $this->locked = false;
    }
}
