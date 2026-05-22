<?php

declare(strict_types=1);

namespace App\Game\Object;

interface UObject
{
    public function getProperty(string $name): mixed;

    public function setProperty(string $name, mixed $value): void;
}
