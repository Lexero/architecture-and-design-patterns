<?php

declare(strict_types=1);

namespace App\ExceptionHandling\Contract;

use Throwable;

interface ExceptionHandlerInterface
{
    public function handle(Throwable $exception, CommandInterface $command): void;
}
