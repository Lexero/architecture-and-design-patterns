<?php

declare(strict_types=1);

namespace App\Tests\ExceptionHandling\Command;

use App\ExceptionHandling\Command\LogCommand;
use App\ExceptionHandling\Contract\CommandInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class LogCommandTest extends TestCase
{
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * Тест для пункта 4: Команда LogCommand записывает информацию об исключении в лог
     */
    public function testLogCommandWritesToLogger(): void
    {
        $exception = new RuntimeException('Error message');
        $failedCommand = $this->createMock(CommandInterface::class);

        $this->logger->expects(self::once())
            ->method('error')
            ->with(
                'Command execution failed',
                self::callback(function (array $context) use ($exception, $failedCommand): bool {
                    return $context['exception'] === RuntimeException::class
                        && $context['message'] === 'Error message'
                        && $context['command'] === get_class($failedCommand);
                })
            );

        $logCommand = new LogCommand($this->logger, $exception, $failedCommand);
        $logCommand->execute();
    }
}
