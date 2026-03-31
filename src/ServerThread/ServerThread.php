<?php

declare(strict_types=1);

namespace App\ServerThread;

use App\ServerThread\Queue\ThreadSafeQueue;
use App\ServerThread\Sync\ManualResetEvent;
use Closure;
use Fiber;
use Throwable;

final class ServerThread
{
    private bool $stop = false;
    private Closure $behaviour;
    private ?Fiber $fiber = null;
    private ?ManualResetEvent $startedEvent = null;
    private ?ManualResetEvent $stoppedEvent = null;

    public function __construct(
        private readonly ThreadSafeQueue $queue,
    ) {
        $this->behaviour = function (): void {
            $command = $this->queue->take(100);

            if ($command === null) {
                return;
            }

            try {
                $command->execute();
            } catch (Throwable $e) {
            }
        };
    }

    public function start(): void
    {
        if ($this->isRunning()) {
            return;
        }

        $this->stop = false;

        $this->fiber = new Fiber(function (): void {
            $this->before();

            while (!$this->stop) {
                ($this->behaviour)();

                Fiber::suspend();
            }

            $this->after();
        });

        $this->fiber->start();
    }

    public function stop(): void
    {
        $this->stop = true;
    }

    public function isRunning(): bool
    {
        return $this->fiber !== null && !$this->fiber->isTerminated();
    }

    public function getFiber(): ?Fiber
    {
        return $this->fiber;
    }

    public function changeBehaviour(Closure $newBehaviour): void
    {
        $this->behaviour = $newBehaviour;
    }

    public function getBehaviour(): Closure
    {
        return $this->behaviour;
    }

    public function getQueue(): ThreadSafeQueue
    {
        return $this->queue;
    }

    public function setStartedEvent(?ManualResetEvent $event): void
    {
        $this->startedEvent = $event;
    }

    public function setStoppedEvent(?ManualResetEvent $event): void
    {
        $this->stoppedEvent = $event;
    }

    private function before(): void
    {
        $this->startedEvent?->signal();
    }

    private function after(): void
    {
        $this->stoppedEvent?->signal();
    }
}
