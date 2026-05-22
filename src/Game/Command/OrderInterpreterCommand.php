<?php

declare(strict_types=1);

namespace App\Game\Command;

use App\Game\Exception\OrderAccessDeniedException;
use App\Game\Object\UObject;
use App\IoC\IoC;
use App\SpaceObject\Contract\CommandInterface;
use RuntimeException;

final class OrderInterpreterCommand implements CommandInterface
{
    public function __construct(
        private readonly UObject $order,
        private readonly string $playerId,
    ) {
    }

    public function execute(): void
    {
        $objectId = $this->order->getProperty('id');
        $action = $this->order->getProperty('action');
        $playerScopeId = "player.{$this->playerId}";
        IoC::resolve('Scopes.Current', $playerScopeId)->execute();

        try {
            $gameObject = IoC::resolve('Game.Objects', $objectId);
        } catch (RuntimeException $e) {
            throw new OrderAccessDeniedException(
                "Player '{$this->playerId}' cannot issue orders to object '{$objectId}': access denied",
                0,
                $e,
            );
        }

        $command = IoC::resolve("Game.Actions.{$action}", $gameObject, $this->order);
        IoC::resolve('Game.Queue.Enqueue', $command)->execute();
    }
}
