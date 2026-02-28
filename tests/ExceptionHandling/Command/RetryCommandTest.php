<?php

declare(strict_types=1);

namespace App\Tests\ExceptionHandling\Command;

use App\ExceptionHandling\Command\RetryCommand;
use App\ExceptionHandling\Contract\CommandInterface;
use PHPUnit\Framework\TestCase;

class RetryCommandTest extends TestCase
{
    /**
     * Тест для пункта 6: Команда RetryCommand повторяет выполнение оригинальной команды
     */
    public function testRetryCommandExecutesOriginalCommand(): void
    {
        $originalCommand = $this->createMock(CommandInterface::class);
        $originalCommand->expects(self::once())
            ->method('execute');

        $retryCommand = new RetryCommand($originalCommand);
        self::assertEquals($originalCommand, $retryCommand->getCommand());
        $retryCommand->execute();
    }
}
