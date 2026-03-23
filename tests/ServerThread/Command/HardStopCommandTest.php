<?php

declare(strict_types=1);

namespace App\Tests\ServerThread\Command;

use App\ServerThread\Command\HardStopCommand;
use App\ServerThread\Queue\ThreadSafeQueue;
use App\ServerThread\ServerThread;
use App\ServerThread\Sync\ManualResetEvent;
use App\SpaceObject\Contract\CommandInterface;
use PHPUnit\Framework\TestCase;

class HardStopCommandTest extends TestCase
{
    private ThreadSafeQueue $queue;
    private ServerThread $serverThread;

    protected function setUp(): void
    {
        $this->queue = new ThreadSafeQueue();
        $this->serverThread = new ServerThread($this->queue);
    }

    public function testHardStopTerminatesThreadImmediately(): void
    {
        $executedCommands = [];

        $stoppedEvent = new ManualResetEvent();
        $this->serverThread->setStoppedEvent($stoppedEvent);

        $command1 = $this->createCommand('cmd1', $executedCommands);
        $command2 = $this->createCommand('cmd2', $executedCommands);
        $hardStopCommand = new HardStopCommand($this->serverThread);
        $command3 = $this->createCommand('cmd3', $executedCommands);
        $command4 = $this->createCommand('cmd4', $executedCommands);

        $this->queue->enqueue($command1);
        $this->queue->enqueue($command2);
        $this->queue->enqueue($hardStopCommand);
        $this->queue->enqueue($command3);
        $this->queue->enqueue($command4);

        $this->serverThread->start();

        for ($i = 0; $i < 20; $i++) {
            $this->tickFiber();
            if ($stoppedEvent->isSignaled()) {
                break;
            }
        }

        self::assertTrue($stoppedEvent->isSignaled());
        self::assertFalse($this->serverThread->isRunning());
        self::assertEquals(['cmd1', 'cmd2'], $executedCommands);
        self::assertFalse($this->queue->isEmpty());
    }

    public function testHardStopWithEmptyQueue(): void
    {
        $stoppedEvent = new ManualResetEvent();
        $this->serverThread->setStoppedEvent($stoppedEvent);

        $hardStopCommand = new HardStopCommand($this->serverThread);
        $this->queue->enqueue($hardStopCommand);

        $this->serverThread->start();

        for ($i = 0; $i < 10; $i++) {
            $this->tickFiber();
            if ($stoppedEvent->isSignaled()) {
                break;
            }
        }

        self::assertTrue($stoppedEvent->isSignaled());
        self::assertFalse($this->serverThread->isRunning());
    }

    private function createCommand(string $id, array &$executedCommands): CommandInterface
    {
        return new class($id, $executedCommands) implements CommandInterface {
            public function __construct(
                private readonly string $id,
                private array &$executedCommands,
            ) {
            }

            public function execute(): void
            {
                $this->executedCommands[] = $this->id;
            }
        };
    }

    private function tickFiber(): void
    {
        $fiber = $this->serverThread->getFiber();
        if ($fiber !== null && !$fiber->isTerminated() && $fiber->isSuspended()) {
            $fiber->resume();
        }
    }
}
