<?php

declare(strict_types=1);

namespace App\Tests\IoC;

use App\IoC\IoC;
use App\IoC\Scope\ThreadLocalScopeStorage;
use Fiber;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ThreadLocalIoCTest extends TestCase
{
    protected function setUp(): void
    {
        ThreadLocalScopeStorage::clearAll();
    }

    protected function tearDown(): void
    {
        ThreadLocalScopeStorage::clearAll();
    }

    public function testFiberExec(): void
    {
        $results = [];

        $fiber1 = new Fiber(function () {
            IoC::resolve('IoC.Register', 'service', fn() => 'fiber-1-value')->execute();
            return IoC::resolve('service');
        });

        $fiber2 = new Fiber(function () {
            IoC::resolve('IoC.Register', 'service', fn() => 'fiber-2-value')->execute();
            return IoC::resolve('service');
        });

        $fiber1->start();
        $results[] = $fiber1->getReturn();

        $fiber2->start();
        $results[] = $fiber2->getReturn();

        self::assertCount(2, $results);
    }

    public function testThreadLocalIsolation(): void
    {
        IoC::resolve('IoC.Register', 'main-service', fn() => 'main')->execute();

        self::assertSame('main', IoC::resolve('main-service'));

        ThreadLocalScopeStorage::clear();

        $this->expectException(RuntimeException::class);
        IoC::resolve('main-service');
    }

    public function testScopeOperations(): void
    {
        IoC::resolve('Scopes.New', 'scope-a')->execute();
        IoC::resolve('Scopes.New', 'scope-b')->execute();

        IoC::resolve('Scopes.Current', 'scope-a')->execute();
        IoC::resolve('IoC.Register', 'api-version', fn() => 'v1')->execute();

        IoC::resolve('Scopes.Current', 'scope-b')->execute();
        IoC::resolve('IoC.Register', 'api-version', fn() => 'v2')->execute();

        IoC::resolve('Scopes.Current', 'scope-a')->execute();
        self::assertSame('v1', IoC::resolve('api-version'));

        IoC::resolve('Scopes.Current', 'scope-b')->execute();
        self::assertSame('v2', IoC::resolve('api-version'));
    }

    public function testLockMechanismRaceConditions(): void
    {
        $sharedResource = 0;

        IoC::resolve('IoC.Register', 'modify-resource', function () use (&$sharedResource) {
            $temp = $sharedResource;
            usleep(1000);
            $sharedResource = $temp + 1;
            return $temp + 1;
        })->execute();

        $result1 = IoC::resolve('modify-resource');
        $result2 = IoC::resolve('modify-resource');

        self::assertSame(1, $result1);
        self::assertSame(2, $result2);
    }
}
