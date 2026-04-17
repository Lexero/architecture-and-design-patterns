<?php

declare(strict_types=1);

namespace App\Tests\ServerThread\State;

use App\ServerThread\Command\State\HardStopStateCommand;
use App\ServerThread\Command\State\MoveToCommand;
use App\ServerThread\Queue\ThreadSafeQueue;
use App\ServerThread\State\MoveToState;
use App\ServerThread\State\NormalState;
use App\SpaceObject\Contract\CommandInterface;
use PHPUnit\Framework\TestCase;

class NormalStateTest extends TestCase
{
    private NormalState $state;

    protected function setUp(): void
    {
        $this->state = NormalState::getInstance();
    }

    public function testHardStopCommandReturnsNull(): void
    {
        $command = new HardStopStateCommand();

        $nextState = $this->state->handle($command);

        self::assertNull($nextState);
    }

    public function testMoveToCommandReturnsMoveToState(): void
    {
        $targetQueue = new ThreadSafeQueue();
        $command = new MoveToCommand($targetQueue);

        $nextState = $this->state->handle($command);

        self::assertInstanceOf(MoveToState::class, $nextState);
    }

    public function testRegularCommandReturnsSameNormalState(): void
    {
        $executed = false;
        $command = new class($executed) implements CommandInterface {
            public function __construct(private bool &$executed)
            {
            }

            public function execute(): void
            {
                $this->executed = true;
            }
        };

        $nextState = $this->state->handle($command);

        self::assertSame($this->state, $nextState);
        self::assertTrue($executed);
    }

    public function testRegularCommandIsExecuted(): void
    {
        $executed = false;
        $command = new class($executed) implements CommandInterface {
            public function __construct(private bool &$executed)
            {
            }

            public function execute(): void
            {
                $this->executed = true;
            }
        };

        $this->state->handle($command);

        self::assertTrue($executed);
    }
}
