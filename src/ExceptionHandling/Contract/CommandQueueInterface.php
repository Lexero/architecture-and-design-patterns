<?php

declare(strict_types=1);

namespace App\ExceptionHandling\Contract;

interface CommandQueueInterface
{
    public function addToQueue(CommandInterface $command): void;

    public function getFromQueue(): ?CommandInterface;

    public function isEmpty(): bool;
}
