<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Domain;

use Ergnuor\SphinxConfig\Domain\ConfigName;
use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConfigNameTest extends TestCase
{
    public function testTrimsName(): void
    {
        $configName = new ConfigName('  preciousConfig  ');

        $this->assertSame('preciousConfig', $configName->value);
    }

    #[DataProvider('blankNameProvider')]
    public function testRejectsBlankName(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ConfigName($name);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function blankNameProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'spaces only' => ['   '];
        yield 'tabs and line breaks only' => ["\t\n"];
    }

    public function testComparesForEquality(): void
    {
        $configName = new ConfigName('preciousConfig');

        $this->assertTrue($configName->equals(new ConfigName('preciousConfig')));
        $this->assertFalse($configName->equals(new ConfigName('otherConfig')));
    }

    public function testConvertsToString(): void
    {
        $configName = new ConfigName('preciousConfig');

        $this->assertSame('preciousConfig', (string) $configName);
    }
}
