<?php

declare(strict_types=1);

namespace App\Tests\Game\Security;

use App\Game\Exception\OperationNotAllowedException;
use App\Game\Security\OperationWhitelist;
use App\SpaceObject\Contract\CommandInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

final class OperationWhitelistTest extends TestCase
{
    private OperationWhitelist $whitelist;

    protected function setUp(): void
    {
        $this->whitelist = new OperationWhitelist();
    }

    public function testGetAllowedOperations(): void
    {
        $closure1 = fn(mixed $gameObject, array $args): CommandInterface => new class implements CommandInterface {
            public function execute(): void
            {
            }
        };
        $closure2 = fn(mixed $gameObject, array $args): CommandInterface => new class implements CommandInterface {
            public function execute(): void
            {
            }
        };

        $this->whitelist->register('movement.start', $closure1);
        $this->whitelist->register('rotation', $closure2);

        $allowed = $this->whitelist->getAllowedOperations();

        self::assertCount(2, $allowed);
        self::assertContains('movement.start', $allowed);
        self::assertContains('rotation', $allowed);
    }

    public function testIsAllowedReturnsTrueForRegisteredOperation(): void
    {
        $closure = fn(mixed $gameObject, array $args): CommandInterface => new class implements CommandInterface {
            public function execute(): void
            {
            }
        };
        $this->whitelist->register('movement.start', $closure);

        self::assertTrue($this->whitelist->isAllowed('movement.start'));
    }

    public function testIsAllowedReturnsFalseForUnregisteredOperation(): void
    {
        self::assertFalse($this->whitelist->isAllowed('unknown.operation'));
    }

    public function testCreateCommandThrowsExceptionForUnregisteredOperation(): void
    {
        $this->expectException(OperationNotAllowedException::class);
        $this->expectExceptionMessage("Operation 'unknown.operation' is not allowed");

        $gameObject = new stdClass();
        $this->whitelist->createCommand('unknown.operation', $gameObject, []);
    }
}
