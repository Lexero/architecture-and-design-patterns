<?php

declare(strict_types=1);

namespace App\Tests\ServerThread\Command;

use App\ServerThread\Command\SoftStopCommand;
use App\ServerThread\Queue\ThreadSafeQueue;
use App\ServerThread\ServerThread;
use App\ServerThread\Sync\ManualResetEvent;
use App\SpaceObject\Contract\CommandInterface;
use PHPUnit\Framework\TestCase;

class SoftStopCommandTest extends TestCase
{
    private ThreadSafeQueue $queue;
    private ServerThread $serverThread;

    protected function setUp(): void
    {
        $this->queue = new ThreadSafeQueue();
        $this->serverThread = new ServerThread($this->queue);
    }

    public function testSoftStopWaitsForAllCommands(): void
    {
        $executedCommands = [];

        $stoppedEvent = new ManualResetEvent();
        $this->serverThread->setStoppedEvent($stoppedEvent);

        $command1 = $this->createCommand('cmd1', $executedCommands);
        $command2 = $this->createCommand('cmd2', $executedCommands);
        $softStopCommand = new SoftStopCommand($this->serverThread);
        $command3 = $this->createCommand('cmd3', $executedCommands);
        $command4 = $this->createCommand('cmd4', $executedCommands);

        $this->queue->enqueue($command1);
        $this->queue->enqueue($command2);
        $this->queue->enqueue($softStopCommand);
        $this->queue->enqueue($command3);
        $this->queue->enqueue($command4);

        $this->serverThread->start();

        for ($i = 0; $i < 30; $i++) {
            $this->tickFiber();
            if ($stoppedEvent->isSignaled()) {
                break;
            }
        }

        self::assertTrue($stoppedEvent->isSignaled());
        self::assertFalse($this->serverThread->isRunning());
        self::assertEquals(['cmd1', 'cmd2', 'cmd3', 'cmd4'], $executedCommands);
        self::assertTrue($this->queue->isEmpty());
    }

    public function testSoftStopWithEmptyQueue(): void
    {
        $stoppedEvent = new ManualResetEvent();
        $this->serverThread->setStoppedEvent($stoppedEvent);

        $softStopCommand = new SoftStopCommand($this->serverThread);
        $this->queue->enqueue($softStopCommand);

        $this->serverThread->start();

        for ($i = 0; $i < 10; $i++) {
            $this->tickFiber();
            if ($stoppedEvent->isSignaled()) {
                break;
            }
        }

        self::assertTrue($stoppedEvent->isSignaled());
        self::assertFalse($this->serverThread->isRunning());
        self::assertTrue($this->queue->isEmpty());
    }

    public function testSoftStopDifferenceCompareWithHardStop(): void
    {
        $executedCommands = [];

        $command1 = $this->createCommand('cmd1', $executedCommands);
        $command2 = $this->createCommand('cmd2', $executedCommands);
        $softStopCommand = new SoftStopCommand($this->serverThread);
        $command3 = $this->createCommand('cmd3', $executedCommands);
        $command4 = $this->createCommand('cmd4', $executedCommands);
        $command5 = $this->createCommand('cmd5', $executedCommands);

        $this->queue->enqueue($command1);
        $this->queue->enqueue($command2);
        $this->queue->enqueue($softStopCommand);
        $this->queue->enqueue($command3);
        $this->queue->enqueue($command4);
        $this->queue->enqueue($command5);

        $this->serverThread->start();

        for ($i = 0; $i < 40; $i++) {
            $this->tickFiber();
            if (!$this->serverThread->isRunning()) {
                break;
            }
        }

        self::assertCount(5, $executedCommands);
        self::assertEquals(['cmd1', 'cmd2', 'cmd3', 'cmd4', 'cmd5'], $executedCommands);
        self::assertTrue($this->queue->isEmpty());
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
