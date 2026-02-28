<?php

declare(strict_types=1);

namespace App\ExceptionHandling\Handler;

use App\ExceptionHandling\Command\LogCommand;
use App\ExceptionHandling\Command\RetryCommand;
use App\ExceptionHandling\Contract\CommandInterface;
use App\ExceptionHandling\Contract\CommandQueueInterface;
use App\ExceptionHandling\Contract\ExceptionHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Пункт 8: Обработчик исключений: при первом выбросе исключения повторить команду,
 * при повторном выбросе исключения записать информацию в лог
 */
class RetryThenLogHandler implements ExceptionHandlerInterface
{
    public function __construct(
        private readonly CommandQueueInterface $queue,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(Throwable $exception, CommandInterface $command): void
    {
        if ($command instanceof RetryCommand) {
            $originalCommand = $command->getCommand();
            $logCommand = new LogCommand($this->logger, $exception, $originalCommand);
            $this->queue->addToQueue($logCommand);
        } else {
            $retryCommand = new RetryCommand($command);
            $this->queue->addToQueue($retryCommand);
        }
    }
}
