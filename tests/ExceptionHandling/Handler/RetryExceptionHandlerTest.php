<?php

declare(strict_types=1);

namespace App\Tests\ExceptionHandling\Handler;

use App\ExceptionHandling\Command\RetryCommand;
use App\ExceptionHandling\Contract\CommandInterface;
use App\ExceptionHandling\Contract\CommandQueueInterface;
use App\ExceptionHandling\Handler\RetryExceptionHandler;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RetryExceptionHandlerTest extends TestCase
{
    private CommandQueueInterface $queue;

    protected function setUp(): void
    {
        $this->queue = $this->createMock(CommandQueueInterface::class);
    }

    /**
     * Тест для пункта 7: RetryExceptionHandler добавляет RetryCommand в очередь
     */
    public function testRetryExceptionHandlerRetryCommand(): void
    {
        $exception = new RuntimeException('Error message');
        $command = $this->createMock(CommandInterface::class);

        $this->queue->expects(self::once())
            ->method('addToQueue')
            ->with(self::isInstanceOf(RetryCommand::class));

        $handler = new RetryExceptionHandler($this->queue);
        $handler->handle($exception, $command);
    }
}
