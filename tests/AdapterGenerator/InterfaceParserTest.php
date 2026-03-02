<?php

declare(strict_types=1);

namespace App\Tests\AdapterGenerator;

use App\AdapterGenerator\InterfaceParser;
use PHPUnit\Framework\TestCase;

class InterfaceParserTest extends TestCase
{
    private InterfaceParser $parser;

    protected function setUp(): void
    {
        $this->parser = new InterfaceParser();
    }

    public function testParseExtractsCompleteInterfaceMetadata(): void
    {
        $result = $this->parser->parse(TestMovableInterface::class);

        self::assertSame('TestMovableInterface', $result['name']);
        self::assertSame('App\Tests\AdapterGenerator', $result['namespace']);
        self::assertSame(TestMovableInterface::class, $result['fullName']);

        self::assertCount(4, $result['methods']);
        $methodNames = array_column($result['methods'], 'name');
        self::assertContains('getPosition', $methodNames);
        self::assertContains('setPosition', $methodNames);
        self::assertContains('getVelocity', $methodNames);
        self::assertContains('finish', $methodNames);

        $getPosition = array_values(array_filter(
            $result['methods'],
            fn($m) => $m['name'] === 'getPosition'
        ))[0];
        self::assertTrue($getPosition['isGetter']);
        self::assertSame('position', $getPosition['property']);
        self::assertSame('App\SpaceObject\ValueObject\Point', $getPosition['returnType']);

        $setPosition = array_values(array_filter(
            $result['methods'],
            fn($m) => $m['name'] === 'setPosition'
        ))[0];
        self::assertTrue($setPosition['isSetter']);
        self::assertSame('position', $setPosition['property']);
        self::assertSame('newValue', $setPosition['parameters'][0]['name']);
        self::assertSame('App\SpaceObject\ValueObject\Point', $setPosition['parameters'][0]['type']);

        $finish = array_values(array_filter(
            $result['methods'],
            fn($m) => $m['name'] === 'finish'
        ))[0];
        self::assertFalse($finish['isGetter']);
        self::assertFalse($finish['isSetter']);
        self::assertNull($finish['property']);
    }
}
