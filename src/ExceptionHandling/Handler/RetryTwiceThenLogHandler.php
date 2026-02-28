<?php

declare(strict_types=1);

namespace App\ExceptionHandling\Handler;

use App\ExceptionHandling\Command\LogCommand;
use App\ExceptionHandling\Command\RetryCommand;
use App\ExceptionHandling\Command\RetryTwiceCommand;
use App\ExceptionHandling\Contract\CommandInterface;
use App\ExceptionHandling\Contract\CommandQueueInterface;
use App\ExceptionHandling\Contract\ExceptionHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Пункт 9: Обработчик исключений: при первом выбросе исключения повторить команду два раза, потом записать в лог
 */
class RetryTwiceThenLogHandler implements ExceptionHandlerInterface
{
    public function __construct(
        private readonly CommandQueueInterface $queue,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(Throwable $exception, CommandInterface $command): void
    {
        if ($command instanceof RetryTwiceCommand) {
            $originalCommand = $command->getCommand();
            $this->queue->addToQueue(new LogCommand($this->logger, $exception, $originalCommand));
        } elseif ($command instanceof RetryCommand) {
            $originalCommand = $command->getCommand();
            $this->queue->addToQueue(new RetryTwiceCommand($originalCommand));
        } else {
            $this->queue->addToQueue(new RetryCommand($command));
        }
    }
}
