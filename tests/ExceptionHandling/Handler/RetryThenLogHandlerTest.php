<?php

declare(strict_types=1);

namespace App\Tests\ExceptionHandling\Handler;

use App\ExceptionHandling\Command\LogCommand;
use App\ExceptionHandling\Command\RetryCommand;
use App\ExceptionHandling\Contract\CommandInterface;
use App\ExceptionHandling\Contract\CommandQueueInterface;
use App\ExceptionHandling\Handler\RetryThenLogHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class RetryThenLogHandlerTest extends TestCase
{
    private CommandQueueInterface $queue;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->queue = $this->createMock(CommandQueueInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * Тест для пункта 8: RetryThenLogHandler повторяет при первом исключении и логирует при повторном
     */
    public function testFirstExceptionEnqueuesRetryAndLogCommand(): void
    {
        $handler = new RetryThenLogHandler($this->queue, $this->logger);
        $exception = new RuntimeException('Test error');
        $originalCommand = $this->createMock(CommandInterface::class);

        $this->queue->expects(self::exactly(2))
            ->method('addToQueue')
            ->willReturnCallback(function ($command) use (&$retryCommand) {
                static $callCount = 0;
                $callCount++;

                if ($callCount === 1) {
                    self::assertInstanceOf(RetryCommand::class, $command);
                    $retryCommand = $command;
                } else {
                    self::assertInstanceOf(LogCommand::class, $command);
                }
            });

        $handler->handle($exception, $originalCommand);
        $handler->handle($exception, $retryCommand);
    }
}
