<?php

declare(strict_types=1);

namespace App\ExceptionHandling\Handler;

use App\ExceptionHandling\Command\RetryCommand;
use App\ExceptionHandling\Contract\CommandInterface;
use App\ExceptionHandling\Contract\CommandQueueInterface;
use App\ExceptionHandling\Contract\ExceptionHandlerInterface;
use Throwable;

/**
 * Пункт 7: Обработчик исключения, который ставит в очередь Команду-повторитель
 */
class RetryExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(
        private readonly CommandQueueInterface $queue,
    ) {
    }

    public function handle(Throwable $exception, CommandInterface $command): void
    {
        $retryCommand = new RetryCommand($command);
        $this->queue->addToQueue($retryCommand);
    }
}
