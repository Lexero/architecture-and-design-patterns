<?php

declare(strict_types=1);

namespace App\ExceptionHandling\Command;

use App\ExceptionHandling\Contract\CommandInterface;

/**
 * Пункт 6: Команда, которая повторяет Команду, выбросившую исключение
 */
class RetryCommand implements CommandInterface
{
    public function __construct(
        private readonly CommandInterface $command,
    ) {
    }

    public function execute(): void
    {
        $this->command->execute();
    }

    public function getCommand(): CommandInterface
    {
        return $this->command;
    }
}
