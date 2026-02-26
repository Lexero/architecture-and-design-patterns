<?php

declare(strict_types=1);

namespace App\ExceptionHandling\Command;

use App\ExceptionHandling\Contract\CommandInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Пункт 4: Команда, которая записывает информацию о выброшенном исключении в лог
 */
class LogCommand implements CommandInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Throwable $exception,
        private readonly CommandInterface $failedCommand,
    ) {
    }

    public function execute(): void
    {
        $this->logger->error('Command execution failed', [
            'exception' => get_class($this->exception),
            'message' => $this->exception->getMessage(),
            'command' => get_class($this->failedCommand),
        ]);
    }
}
