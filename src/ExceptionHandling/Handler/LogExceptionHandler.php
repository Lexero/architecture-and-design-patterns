<?php

declare(strict_types=1);

namespace App\ExceptionHandling\Handler;

use App\ExceptionHandling\Command\LogCommand;
use App\ExceptionHandling\Contract\CommandInterface;
use App\ExceptionHandling\Contract\CommandQueueInterface;
use App\ExceptionHandling\Contract\ExceptionHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Пункт 5: Обработчик исключения, который ставит Команду, пишущую в лог, в очередь Команд
 */
class LogExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(
        private readonly CommandQueueInterface $queue,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(Throwable $exception, CommandInterface $command): void
    {
        $logCommand = new LogCommand($this->logger, $exception, $command);
        $this->queue->addToQueue($logCommand);
    }
}
