<?php

declare(strict_types=1);

namespace App\Game\Endpoint;

use App\IoC\IoC;
use App\Game\Command\InterpretCommand;
use App\Game\DTO\GameMessage;
use App\Game\GameManager;
use App\Game\Security\OperationWhitelist;
use App\SpaceObject\Contract\CommandInterface;
use InvalidArgumentException;
use RuntimeException;

final class MessageEndpoint
{
    public function __construct(
        private readonly GameManager $gameManager,
        private readonly OperationWhitelist $whitelist,
    ) {
    }

    public function handleMessage(GameMessage $message): void
    {
        $game = $this->gameManager->getGame($message->gameId);

        $currentScopeCommand = IoC::resolve('Scopes.Current', $game->getScopeId());
        $currentScopeCommand->execute();

        $interpretCommand = new InterpretCommand($message, $this->whitelist);
        $enqueueCommand = IoC::resolve('Game.Queue.Enqueue', $interpretCommand);

        if (!$enqueueCommand instanceof CommandInterface) {
            throw new RuntimeException('Game.Queue.Enqueue did not resolve to a CommandInterface');
        }

        $enqueueCommand->execute();

        $rootScopeCommand = IoC::resolve('Scopes.Current', 'root');
        $rootScopeCommand->execute();
    }

    public function handleJsonMessage(string $json): void
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new InvalidArgumentException('Invalid JSON message format');
        }

        $message = GameMessage::fromArray($data);
        $this->handleMessage($message);
    }
}
