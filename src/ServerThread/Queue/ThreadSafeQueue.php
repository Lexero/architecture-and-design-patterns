<?php

declare(strict_types=1);

namespace App\ServerThread\Queue;

use App\SpaceObject\Contract\CommandInterface;
use SplQueue;

final class ThreadSafeQueue
{
    private readonly SplQueue $queue;
    private bool $locked = false;

    public function __construct()
    {
        $this->queue = new SplQueue();
    }

    public function enqueue(CommandInterface $command): void
    {
        $this->lock();
        try {
            $this->queue->enqueue($command);
        } finally {
            $this->unlock();
        }
    }

    public function dequeue(): ?CommandInterface
    {
        $this->lock();
        try {
            if ($this->queue->isEmpty()) {
                return null;
            }

            return $this->queue->dequeue();
        } finally {
            $this->unlock();
        }
    }

    public function take(int $timeoutMs = 5000): ?CommandInterface
    {
        $startTime = microtime(true);
        $timeoutSec = $timeoutMs / 1000.0;

        while (true) {
            $command = $this->dequeue();
            if ($command !== null) {
                return $command;
            }

            $elapsed = microtime(true) - $startTime;
            if ($elapsed >= $timeoutSec) {
                return null;
            }

            usleep(100);
        }
    }

    public function isEmpty(): bool
    {
        $this->lock();
        try {
            return $this->queue->isEmpty();
        } finally {
            $this->unlock();
        }
    }

    public function count(): int
    {
        $this->lock();
        try {
            return $this->queue->count();
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
