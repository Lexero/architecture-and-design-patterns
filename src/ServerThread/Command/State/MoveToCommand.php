<?php

declare(strict_types=1);

namespace App\ServerThread\Command\State;

use App\ServerThread\Queue\ThreadSafeQueue;
use App\SpaceObject\Contract\CommandInterface;

final class MoveToCommand implements CommandInterface
{
    public function __construct(
        private readonly ThreadSafeQueue $targetQueue,
    ) {
    }

    public function getTargetQueue(): ThreadSafeQueue
    {
        return $this->targetQueue;
    }

    public function execute(): void
    {
    }
}
