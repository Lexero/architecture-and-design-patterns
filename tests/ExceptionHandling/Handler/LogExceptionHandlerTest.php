<?php

declare(strict_types=1);

namespace App\Tests\ExceptionHandling\Handler;

use App\ExceptionHandling\Command\LogCommand;
use App\ExceptionHandling\Contract\CommandInterface;
use App\ExceptionHandling\Contract\CommandQueueInterface;
use App\ExceptionHandling\Handler\LogExceptionHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class LogExceptionHandlerTest extends TestCase
{
    private CommandQueueInterface $queue;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->queue = $this->createMock(CommandQueueInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * Тест для пункта 5: LogExceptionHandler добавляет LogCommand в очередь
     */
    public function testLogExceptionHandlerLogCommand(): void
    {
        $exception = new RuntimeException('Error message');
        $command = $this->createMock(CommandInterface::class);

        $this->queue->expects(self::once())
            ->method('addToQueue')
            ->with(self::isInstanceOf(LogCommand::class));

        $handler = new LogExceptionHandler($this->queue, $this->logger);
        $handler->handle($exception, $command);
    }
}
