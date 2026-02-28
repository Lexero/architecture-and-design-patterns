<?php

declare(strict_types=1);

namespace App\ExceptionHandling\Queue;

use App\ExceptionHandling\Contract\CommandInterface;
use App\ExceptionHandling\Contract\CommandQueueInterface;
use App\ExceptionHandling\Contract\ExceptionHandlerInterface;
use Throwable;

class CommandQueueProcessor
{
    public function __construct(
        private readonly CommandQueueInterface $queue,
        private readonly ExceptionHandlerInterface $exceptionHandler,
    ) {
    }

    public function processAll(): void
    {
        while (!$this->queue->isEmpty()) {
            $this->processNext();
        }
    }

    public function processNext(): void
    {
        $command = $this->queue->getFromQueue();

        if ($command === null) {
            return;
        }

        $this->executeCommand($command);
    }

    private function executeCommand(CommandInterface $command): void
    {
        try {
            $command->execute(); // Пункт 1: Обернуть вызов Команды в блок try-catch
        } catch (Throwable $exception) { // Пункт 2: Обработчик catch должен перехватывать только самое базовое исключение
            $this->exceptionHandler->handle($exception, $command); // Пункт 3: Есть множество различных обработчиков исключений
            // Выбор подходящего обработчика исключения делается на основе экземпляра перехваченного исключения и команды, которая выбросила исключение
        }
    }
}
