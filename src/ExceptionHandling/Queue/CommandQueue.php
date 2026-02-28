<?php

declare(strict_types=1);

namespace App\ExceptionHandling\Queue;

use App\ExceptionHandling\Contract\CommandInterface;
use App\ExceptionHandling\Contract\CommandQueueInterface;
use SplQueue;

class CommandQueue implements CommandQueueInterface
{
    private SplQueue $queue;

    public function __construct()
    {
        $this->queue = new SplQueue();
    }

    public function addToQueue(CommandInterface $command): void
    {
        $this->queue->enqueue($command);
    }

    public function getFromQueue(): ?CommandInterface
    {
        if ($this->queue->isEmpty()) {
            return null;
        }

        return $this->queue->dequeue();
    }

    public function isEmpty(): bool
    {
        return $this->queue->isEmpty();
    }
}
