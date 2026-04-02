<?php

declare(strict_types=1);

namespace App\Tests\Game\Endpoint;

use App\IoC\IoC;
use App\Game\DTO\GameMessage;
use App\Game\Endpoint\MessageEndpoint;
use App\Game\Exception\GameNotFoundException;
use App\Game\Game;
use App\Game\GameManager;
use App\Game\Security\OperationWhitelist;
use App\ServerThread\Queue\ThreadSafeQueue;
use App\ServerThread\ServerThread;
use App\SpaceObject\Contract\CommandInterface;
use JsonException;
use PHPUnit\Framework\TestCase;
use stdClass;

final class MessageEndpointTest extends TestCase
{
    private GameManager $gameManager;
    private MessageEndpoint $endpoint;

    protected function setUp(): void
    {
        IoC::reset();
        $newScopeCommand = IoC::resolve('Scopes.New', 'root');
        $newScopeCommand->execute();

        $currentScopeCommand = IoC::resolve('Scopes.Current', 'root');
        $currentScopeCommand->execute();

        $this->gameManager = new GameManager();
        $whitelist = new OperationWhitelist();
        $this->endpoint = new MessageEndpoint($this->gameManager, $whitelist);

        $whitelist->register('movement.start', function (mixed $gameObject, array $args): CommandInterface {
            return new class implements CommandInterface {
                public function execute(): void
                {
                }
            };
        });
    }

    protected function tearDown(): void
    {
        IoC::reset();
    }

    public function testHandleMessageValidMessage(): void
    {
        $game = $this->createGame();
        $this->gameManager->addGame($game);

        $commandExecuted = false;
        $this->setupGameScope($commandExecuted);

        $message = new GameMessage(
            'game-1',
            'obj-123',
            'movement.start',
            ['speed' => 10]
        );

        $this->endpoint->handleMessage($message);
        self::assertTrue($commandExecuted);
    }

    public function testHandleMessageThrowsExceptionWhenGameNotFound(): void
    {
        $message = new GameMessage(
            'non-existent',
            'obj-123',
            'movement.start',
        );

        $this->expectException(GameNotFoundException::class);
        $this->expectExceptionMessage("Game with ID 'non-existent' not found");
        $this->endpoint->handleMessage($message);
    }

    public function testHandleMessageParsesAndProcessesJson(): void
    {
        $game = $this->createGame();
        $this->gameManager->addGame($game);

        $commandExecuted = false;
        $this->setupGameScope($commandExecuted);

        $json = json_encode([
            'gameId' => 'game-1',
            'objectId' => 'obj-123',
            'operationId' => 'movement.start',
            'args' => ['speed' => 10],
        ]);

        $this->endpoint->handleJsonMessage($json);
        self::assertTrue($commandExecuted);
    }

    public function testHandleJsonMessageThrowsExceptionOnInvalidJson(): void
    {
        $this->expectException(JsonException::class);

        $this->endpoint->handleJsonMessage('invalid json {');
    }


    private function createGame(): Game
    {
        $queue = new ThreadSafeQueue();
        $thread = new ServerThread($queue);
        return new Game('game-1', "game.game-1", $thread);
    }


    private function setupGameScope(bool &$commandExecuted): void
    {
        $game = $this->gameManager->getGame('game-1');

        $newScopeCommand = IoC::resolve('Scopes.New', $game->getScopeId(), 'root');
        $newScopeCommand->execute();

        $currentScopeCommand = IoC::resolve('Scopes.Current', $game->getScopeId());
        $currentScopeCommand->execute();

        $gameObject = new stdClass();
        $gameObject->id = 'obj-123';

        IoC::resolve('IoC.Register', 'Game.Objects', fn() => $gameObject)->execute();
        IoC::resolve('IoC.Register', 'Game.Queue', fn() => $game->getThread()->getQueue())->execute();
        IoC::resolve('IoC.Register', 'Game.Queue.Enqueue', function (CommandInterface $command) use (&$commandExecuted) {
            return new class($command, $commandExecuted) implements CommandInterface {
                public function __construct(
                    private readonly CommandInterface $command,
                    private bool &$executed
                ) {
                }

                public function execute(): void
                {
                    $this->command->execute();
                    $this->executed = true;
                }
            };
        })->execute();

        $rootScopeCommand = IoC::resolve('Scopes.Current', 'root');
        $rootScopeCommand->execute();
    }
}
