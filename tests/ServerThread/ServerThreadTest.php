<?php

declare(strict_types=1);

namespace App\Tests\ServerThread;

use App\ServerThread\Queue\ThreadSafeQueue;
use App\ServerThread\ServerThread;
use App\ServerThread\Sync\ManualResetEvent;
use App\SpaceObject\Contract\CommandInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ServerThreadTest extends TestCase
{
    private ThreadSafeQueue $queue;
    private ServerThread $serverThread;

    protected function setUp(): void
    {
        $this->queue = new ThreadSafeQueue();
        $this->serverThread = new ServerThread($this->queue);
    }

    public function testStartThreadWithManualResetEvent(): void
    {
        $startedEvent = new ManualResetEvent();
        $this->serverThread->setStartedEvent($startedEvent);
        $this->serverThread->start();

        $signaled = $startedEvent->waitOne(1000);
        self::assertTrue($signaled);
        self::assertTrue($this->serverThread->isRunning());

        $this->serverThread->stop();
        $this->tickFiber();
    }

    public function testCommandExecutionInThread(): void
    {
        $executedCommands = [];

        $command1 = $this->createCommand('cmd1', $executedCommands);
        $command2 = $this->createCommand('cmd2', $executedCommands);
        $command3 = $this->createCommand('cmd3', $executedCommands);

        $this->queue->enqueue($command1);
        $this->queue->enqueue($command2);
        $this->queue->enqueue($command3);

        $this->serverThread->start();

        for ($i = 0; $i < 10; $i++) {
            $this->tickFiber();
            if (count($executedCommands) === 3) {
                break;
            }
        }

        self::assertCount(3, $executedCommands);
        self::assertEquals(['cmd1', 'cmd2', 'cmd3'], $executedCommands);

        $this->serverThread->stop();
        $this->tickFiber();
    }

    public function testExceptionHandlingInThread(): void
    {
        $executedCommands = [];

        $command1 = $this->createCommand('cmd1', $executedCommands);
        $command2 = $this->createCommandWithException();
        $command3 = $this->createCommand('cmd3', $executedCommands);

        $this->queue->enqueue($command1);
        $this->queue->enqueue($command2);
        $this->queue->enqueue($command3);

        $this->serverThread->start();

        for ($i = 0; $i < 10; $i++) {
            $this->tickFiber();
            if (count($executedCommands) === 2) {
                break;
            }
        }

        self::assertCount(2, $executedCommands);
        self::assertEquals(['cmd1', 'cmd3'], $executedCommands);
        self::assertTrue($this->serverThread->isRunning());

        $this->serverThread->stop();
        $this->tickFiber();
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

    private function createCommandWithException(): CommandInterface
    {
        return new class implements CommandInterface {
            public function execute(): void
            {
                throw new RuntimeException();
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
