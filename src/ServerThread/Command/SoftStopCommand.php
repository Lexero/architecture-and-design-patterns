<?php

declare(strict_types=1);

namespace App\ServerThread\Command;

use App\ServerThread\ServerThread;
use App\SpaceObject\Contract\CommandInterface;

final class SoftStopCommand implements CommandInterface
{
    public function __construct(
        private readonly ServerThread $serverThread,
    ) {
    }

    public function execute(): void
    {
        $oldBehaviour = $this->serverThread->getBehaviour();
        $queue = $this->serverThread->getQueue();

        $newBehaviour = function () use ($oldBehaviour, $queue): void {
            if ($queue->count() > 0) {
                $oldBehaviour();
            } else {
                $this->serverThread->stop();
            }
        };

        $this->serverThread->changeBehaviour($newBehaviour);
    }
}
