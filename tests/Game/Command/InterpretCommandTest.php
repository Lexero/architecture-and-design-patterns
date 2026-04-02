<?php

declare(strict_types=1);

namespace App\Tests\Game\Command;

use App\IoC\IoC;
use App\Game\Command\InterpretCommand;
use App\Game\DTO\GameMessage;
use App\Game\Exception\OperationNotAllowedException;
use App\Game\Security\OperationWhitelist;
use App\SpaceObject\Contract\CommandInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

final class InterpretCommandTest extends TestCase
{
    private OperationWhitelist $whitelist;

    protected function setUp(): void
    {
        IoC::reset();
        $this->setupIoC();

        $this->whitelist = new OperationWhitelist();
    }

    protected function tearDown(): void
    {
        IoC::reset();
    }

    public function testInterpretCommandUsesWhitelistToCreateCommand(): void
    {
        $executed = false;
        $gameObject = new stdClass();
        $gameObject->id = 'obj-123';

        IoC::resolve('IoC.Register', 'Game.Objects', function (string $objectId) use ($gameObject) {
            self::assertSame('obj-123', $objectId);
            return $gameObject;
        })->execute();

        IoC::resolve('IoC.Register', 'Game.Queue.Enqueue', function (CommandInterface $command) use (&$executed) {
            return new class($executed) implements CommandInterface {
                public function __construct(private bool &$executed)
                {
                }

                public function execute(): void
                {
                    $this->executed = true;
                }
            };
        })->execute();

        $this->whitelist->register('movement.start', function (mixed $gameObject, array $args): CommandInterface {
            return new class implements CommandInterface {
                public function execute(): void
                {
                }
            };
        });

        $message = new GameMessage(
            'game-1',
            'obj-123',
            'movement.start',
            ['speed' => 10]
        );

        $interpretCommand = new InterpretCommand($message, $this->whitelist);
        $interpretCommand->execute();

        self::assertTrue($executed, 'Command should have been enqueued');
    }

    public function testInterpretCommandRejectsUnwhitelistedOperation(): void
    {
        $message = new GameMessage(
            'game-1',
            'obj-123',
            'system.deleteAll',
        );

        $this->expectException(OperationNotAllowedException::class);
        $this->expectExceptionMessage("Operation 'system.deleteAll' is not allowed");

        $gameObject = new stdClass();
        IoC::resolve('IoC.Register', 'Game.Objects', fn(string $objectId) => $gameObject)->execute();

        $interpretCommand = new InterpretCommand($message, $this->whitelist);
        $interpretCommand->execute();
    }

    private function setupIoC(): void
    {
        $newScopeCommand = IoC::resolve('Scopes.New', 'root');
        $newScopeCommand->execute();

        $currentScopeCommand = IoC::resolve('Scopes.Current', 'root');
        $currentScopeCommand->execute();
    }
}
