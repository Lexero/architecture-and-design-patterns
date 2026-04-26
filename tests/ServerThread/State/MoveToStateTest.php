<?php

declare(strict_types=1);

namespace App\Tests\ServerThread\State;

use App\ServerThread\Command\State\HardStopStateCommand;
use App\ServerThread\Command\State\RunCommand;
use App\ServerThread\Queue\ThreadSafeQueue;
use App\ServerThread\State\MoveToState;
use App\ServerThread\State\NormalState;
use App\SpaceObject\Contract\CommandInterface;
use PHPUnit\Framework\TestCase;

class MoveToStateTest extends TestCase
{
    private ThreadSafeQueue $targetQueue;
    private MoveToState $state;

    protected function setUp(): void
    {
        $this->targetQueue = new ThreadSafeQueue();
        $this->state = new MoveToState($this->targetQueue);
    }

    public function testHardStopCommandReturnsNull(): void
    {
        $command = new HardStopStateCommand();

        $nextState = $this->state->handle($command);

        self::assertNull($nextState);
    }

    public function testRunCommandReturnsNormalState(): void
    {
        $command = new RunCommand();

        $nextState = $this->state->handle($command);

        self::assertInstanceOf(NormalState::class, $nextState);
    }

    public function testRegularCommandIsRedirectedToTargetQueue(): void
    {
        $command = new class implements CommandInterface {
            public function execute(): void
            {
            }
        };

        $this->state->handle($command);

        self::assertSame($command, $this->targetQueue->dequeue());
    }

    public function testRegularCommandReturnsSameMoveToState(): void
    {
        $command = new class implements CommandInterface {
            public function execute(): void
            {
            }
        };

        $nextState = $this->state->handle($command);

        self::assertSame($this->state, $nextState);
    }
}
