<?php

declare(strict_types=1);

namespace App\SpaceObject\Exception;

use RuntimeException;

class DirectionWriteException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Unable to write object direction');
    }
}
