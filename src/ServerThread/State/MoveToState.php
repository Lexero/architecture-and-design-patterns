<?php

declare(strict_types=1);

namespace App\ServerThread\State;

use App\ServerThread\Command\State\HardStopStateCommand;
use App\ServerThread\Command\State\RunCommand;
use App\ServerThread\Queue\ThreadSafeQueue;
use App\SpaceObject\Contract\CommandInterface;

final class MoveToState implements ThreadStateInterface
{
    public function __construct(
        private readonly ThreadSafeQueue $targetQueue,
    ) {
    }

    public function handle(CommandInterface $command): ?ThreadStateInterface
    {
        if ($command instanceof HardStopStateCommand) {
            return null;
        }

        if ($command instanceof RunCommand) {
            return NormalState::getInstance();
        }

        $this->targetQueue->enqueue($command);

        return $this;
    }
}
