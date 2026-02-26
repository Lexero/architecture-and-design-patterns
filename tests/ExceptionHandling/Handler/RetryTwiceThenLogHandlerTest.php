<?php

declare(strict_types=1);

namespace App\Tests\ExceptionHandling\Handler;

use App\ExceptionHandling\Command\LogCommand;
use App\ExceptionHandling\Command\RetryCommand;
use App\ExceptionHandling\Command\RetryTwiceCommand;
use App\ExceptionHandling\Contract\CommandInterface;
use App\ExceptionHandling\Contract\CommandQueueInterface;
use App\ExceptionHandling\Handler\RetryTwiceThenLogHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class RetryTwiceThenLogHandlerTest extends TestCase
{
    private CommandQueueInterface $queue;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->queue = $this->createMock(CommandQueueInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * Тест для пункта 9: RetryTwiceThenLogHandler повторяет два раза и логирует при последующем
     */
    public function testFirstExceptionEnqueuesRetryTwiceAndLogCommand(): void
    {
        $handler = new RetryTwiceThenLogHandler($this->queue, $this->logger);
        $exception = new RuntimeException('Test error');
        $originalCommand = $this->createMock(CommandInterface::class);

        $this->queue->expects(self::exactly(3))
            ->method('addToQueue')
            ->willReturnCallback(function ($command) use (&$retryCommand, &$retryTwiceCommand) {
                static $callCount = 0;
                $callCount++;

                if ($callCount === 1) {
                    self::assertInstanceOf(RetryCommand::class, $command);
                    $retryCommand = $command;
                } elseif ($callCount === 2) {
                    self::assertInstanceOf(RetryTwiceCommand::class, $command);
                    $retryTwiceCommand = $command;
                } else {
                    self::assertInstanceOf(LogCommand::class, $command);
                }
            });

        $handler->handle($exception, $originalCommand);
        $handler->handle($exception, $retryCommand);
        $handler->handle($exception, $retryTwiceCommand);
    }
}
