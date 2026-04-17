<?php

declare(strict_types=1);

namespace App\ServerThread\State;

use App\ServerThread\Command\State\HardStopStateCommand;
use App\ServerThread\Command\State\MoveToCommand;
use App\SpaceObject\Contract\CommandInterface;

final class NormalState implements ThreadStateInterface
{
    private static ?NormalState $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function handle(CommandInterface $command): ?ThreadStateInterface
    {
        $command->execute();

        if ($command instanceof HardStopStateCommand) {
            return null;
        }

        if ($command instanceof MoveToCommand) {
            return new MoveToState($command->getTargetQueue());
        }

        return $this;
    }
}
