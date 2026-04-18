<?php

declare(strict_types=1);

namespace App\Tests\Game\Command;

use App\Game\Command\OrderInterpreterCommand;
use App\Game\Exception\OrderAccessDeniedException;
use App\Game\Object\UObjectImpl;
use App\IoC\IoC;
use App\SpaceObject\Contract\CommandInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class OrderInterpreterCommandTest extends TestCase
{
    protected function setUp(): void
    {
        IoC::reset();
        IoC::resolve('Scopes.New', 'root')->execute();
        IoC::resolve('Scopes.Current', 'root')->execute();
    }

    protected function tearDown(): void
    {
        IoC::reset();
    }

    public function testExecuteResolvesActionAndEnqueuesCommand(): void
    {
        $this->setupPlayerScope('player1');

        $enqueued = false;
        $this->registerEnqueueInPlayerScope('player1', $enqueued);

        $gameObject = new UObjectImpl(['id' => 'obj-1']);
        $this->registerObjectInPlayerScope('player1', 'obj-1', $gameObject);

        $actionExecuted = false;
        $this->registerActionInPlayerScope('player1', 'StartMove', function () use (&$actionExecuted): CommandInterface {
            return new class($actionExecuted) implements CommandInterface {
                public function __construct(private bool &$executed) {}
                public function execute(): void { $this->executed = true; }
            };
        });

        $order = new UObjectImpl(['id' => 'obj-1', 'action' => 'StartMove', 'initialVelocity' => 2]);
        $command = new OrderInterpreterCommand($order, 'player1');
        $command->execute();

        self::assertTrue($enqueued, 'The command must be queued');
    }

    public function testExecuteWorksWithAnyAction(): void
    {
        $this->setupPlayerScope('player1');

        $enqueued = false;
        $this->registerEnqueueInPlayerScope('player1', $enqueued);

        $gameObject = new UObjectImpl(['id' => 'obj-1']);
        $this->registerObjectInPlayerScope('player1', 'obj-1', $gameObject);

        foreach (['StopMove', 'Shoot', 'Rotate', 'ActivateShield', 'AnyFutureAction'] as $action) {
            $enqueued = false;
            $this->registerActionInPlayerScope('player1', $action, function (): CommandInterface {
                return new class implements CommandInterface {
                    public function execute(): void {}
                };
            });

            $order = new UObjectImpl(['id' => 'obj-1', 'action' => $action]);
            new OrderInterpreterCommand($order, 'player1')->execute();
            self::assertTrue($enqueued, "The action {$action} must be processed");
        }
    }

    public function testExecuteThrowsWhenPlayerIssuesOrderToAnotherPlayersObject(): void
    {
        $this->setupPlayerScope('player1');
        $gameObject = new UObjectImpl(['id' => 'obj-1']);
        $this->registerObjectInPlayerScope('player1', 'obj-1', $gameObject);
        $this->setupPlayerScope('player2');

        $order = new UObjectImpl(['id' => 'obj-1', 'action' => 'Shoot']);
        $command = new OrderInterpreterCommand($order, 'player2');

        $this->expectException(OrderAccessDeniedException::class);
        $this->expectExceptionMessage("Player 'player2' cannot issue orders to object 'obj-1'");
        $command->execute();
    }

    public function testExecuteSucceedsWhenPlayerIssuesOrderToOwnObject(): void
    {
        $this->setupPlayerScope('player2');

        $enqueued = false;
        $this->registerEnqueueInPlayerScope('player2', $enqueued);

        $gameObject = new UObjectImpl(['id' => 'obj-2']);
        $this->registerObjectInPlayerScope('player2', 'obj-2', $gameObject);
        $this->registerActionInPlayerScope('player2', 'Rotate', function (): CommandInterface {
            return new class implements CommandInterface {
                public function execute(): void {}
            };
        });

        $order = new UObjectImpl(['id' => 'obj-2', 'action' => 'Rotate']);
        new OrderInterpreterCommand($order, 'player2')->execute();

        self::assertTrue($enqueued);
    }

    public function testExecuteHandlesNonGameObjectOrdersUsingIoC(): void
    {
        $this->setupPlayerScope('player1');

        $enqueued = false;
        $this->registerEnqueueInPlayerScope('player1', $enqueued);

        $systemContext = new UObjectImpl(['saveName' => 'slot1']);
        $this->registerObjectInPlayerScope('player1', 'system', $systemContext);

        $this->registerActionInPlayerScope('player1', 'SaveGame', function (): CommandInterface {
            return new class implements CommandInterface {
                public function execute(): void {}
            };
        });

        $order = new UObjectImpl(['id' => 'system', 'action' => 'SaveGame', 'saveName' => 'slot1']);
        new OrderInterpreterCommand($order, 'player1')->execute();

        self::assertTrue($enqueued);
    }

    public function testExecuteThrowsWhenActionNotRegistered(): void
    {
        $this->setupPlayerScope('player1');
        $gameObject = new UObjectImpl(['id' => 'obj-1']);
        $this->registerObjectInPlayerScope('player1', 'obj-1', $gameObject);

        $order = new UObjectImpl(['id' => 'obj-1', 'action' => 'UnknownAction']);
        $command = new OrderInterpreterCommand($order, 'player1');

        $this->expectException(RuntimeException::class);
        $command->execute();
    }


    private function setupPlayerScope(string $playerId): void
    {
        IoC::resolve('Scopes.New', "player.{$playerId}", 'root')->execute();
    }

    private function registerObjectInPlayerScope(string $playerId, string $objectId, mixed $object): void
    {
        $scopeId = "player.{$playerId}";
        IoC::resolve('Scopes.Current', $scopeId)->execute();
        IoC::resolve('IoC.Register', "Game.Objects", fn(string $id) => match ($id) {
            $objectId => $object,
            default => throw new RuntimeException("Object '{$id}' not found in scope '{$scopeId}'"),
        })->execute();
        IoC::resolve('Scopes.Current', 'root')->execute();
    }

    private function registerEnqueueInPlayerScope(string $playerId, bool &$enqueued): void
    {
        IoC::resolve('Scopes.Current', "player.{$playerId}")->execute();
        IoC::resolve('IoC.Register', 'Game.Queue.Enqueue', function (CommandInterface $cmd) use (&$enqueued): CommandInterface {
            return new class($enqueued, $cmd) implements CommandInterface {
                public function __construct(private bool &$enqueued, private CommandInterface $cmd) {}
                public function execute(): void {
                    $this->enqueued = true;
                    $this->cmd->execute();
                }
            };
        })->execute();
        IoC::resolve('Scopes.Current', 'root')->execute();
    }

    private function registerActionInPlayerScope(string $playerId, string $action, callable $factory): void
    {
        IoC::resolve('Scopes.Current', "player.{$playerId}")->execute();
        IoC::resolve('IoC.Register', "Game.Actions.{$action}", $factory)->execute();
        IoC::resolve('Scopes.Current', 'root')->execute();
    }
}
