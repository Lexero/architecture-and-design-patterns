<?php

declare(strict_types=1);

namespace App\ExceptionHandling\Contract;

interface CommandInterface
{
    public function execute(): void;
}
