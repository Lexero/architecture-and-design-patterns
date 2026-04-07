<?php

declare(strict_types=1);

namespace App\Game\Command;

use App\IoC\IoC;
use App\Game\DTO\GameMessage;
use App\Game\Security\OperationWhitelist;
use App\SpaceObject\Contract\CommandInterface;

final class InterpretCommand implements CommandInterface
{
    public function __construct(
        private readonly GameMessage $message,
        private readonly OperationWhitelist $whitelist,
    ) {
    }

    public function execute(): void
    {
        $gameObject = IoC::resolve('Game.Objects', $this->message->objectId);

        $command = $this->whitelist->createCommand(
            $this->message->operationId,
            $gameObject,
            $this->message->args
        );

        $enqueueCommand = IoC::resolve('Game.Queue.Enqueue', $command);
        $enqueueCommand->execute();
    }
}
