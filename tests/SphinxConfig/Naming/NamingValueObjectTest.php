<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Naming;

use Closure;
use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;
use Ergnuor\SphinxConfig\Naming\AbstractNotEmptyString;
use Ergnuor\SphinxConfig\Naming\Alias;
use Ergnuor\SphinxConfig\Naming\BlockName;
use Ergnuor\SphinxConfig\Naming\ConfigName;
use Ergnuor\SphinxConfig\Naming\ParameterName;
use Ergnuor\SphinxConfig\Naming\SectionName;
use Error;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NamingValueObjectTest extends TestCase
{
    /**
     * @param Closure(string): AbstractNotEmptyString $create
     */
    #[DataProvider('valueObjectNormalizationProvider')]
    public function testNormalizesValue(Closure $create, string $input, string $expected): void
    {
        $name = $create($input);

        $this->assertSame($expected, $name->value);
    }

    /**
     * @param Closure(string): AbstractNotEmptyString $create
     */
    #[DataProvider('valueObjectBlankValueProvider')]
    public function testRejectsBlankValue(Closure $create, string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $create($value);
    }

    /**
     * @param Closure(string): AbstractNotEmptyString $create
     */
    #[DataProvider('valueObjectNormalizationProvider')]
    public function testConvertsToString(Closure $create, string $input, string $expected): void
    {
        $name = $create($input);

        $this->assertSame($expected, (string) $name);
    }

    /**
     * @param Closure(string): AbstractNotEmptyString $create
     */
    #[DataProvider('valueObjectProvider')]
    public function testValueCannotBeChanged(Closure $create): void
    {
        $name = $create('original');

        $this->expectException(Error::class);

        // @phpstan-ignore assign.propertyProtectedSet (Intentionally writing to verify that the value cannot be changed)
        $name->value = 'changed';
    }

    #[DataProvider('equalityProvider')]
    public function testComparesConfigNamesForEquality(string $left, string $right, bool $expected): void
    {
        $name = new ConfigName($left);

        $this->assertSame($expected, $name->equals(new ConfigName($right)));
    }

    #[DataProvider('equalityProvider')]
    public function testComparesSectionNamesForEquality(string $left, string $right, bool $expected): void
    {
        $name = new SectionName($left);

        $this->assertSame($expected, $name->equals(new SectionName($right)));
    }

    #[DataProvider('equalityProvider')]
    public function testComparesBlockNamesForEquality(string $left, string $right, bool $expected): void
    {
        $name = new BlockName($left);

        $this->assertSame($expected, $name->equals(new BlockName($right)));
    }

    #[DataProvider('equalityProvider')]
    public function testComparesParameterNamesForEquality(string $left, string $right, bool $expected): void
    {
        $name = new ParameterName($left);

        $this->assertSame($expected, $name->equals(new ParameterName($right)));
    }

    #[DataProvider('equalityProvider')]
    public function testComparesAliasesForEquality(string $left, string $right, bool $expected): void
    {
        $alias = new Alias($left);

        $this->assertSame($expected, $alias->equals(new Alias($right)));
    }

    /**
     * @return iterable<string, array{string, string, bool}>
     */
    public static function equalityProvider(): iterable
    {
        yield 'same value' => ['Example  Name', 'Example  Name', true];
        yield 'normalized equality' => [" \tExample  Name\n", 'Example  Name', true];
        yield 'different values' => ['Example  Name', 'Other  Name', false];
        yield 'different case' => ['Example  Name', 'example  Name', false];
        yield 'different inner spaces' => ['Example  Name', 'Example Name', false];
        yield 'numeric strings' => ['01', '1', false];
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function normalizationProvider(): iterable
    {
        yield 'spaces' => ['  Name  ', 'Name'];
        yield 'tabs' => ["\tOther\t", 'Other'];
        yield 'mixed whitespace' => [" \tMiXeD  Name \n", 'MiXeD  Name'];

        $value = 'MiXeD';
        yield 'preserves case' => [$value, $value];

        $value = "Name  With\tSpaces";
        yield 'preserves inner whitespace' => [$value, $value];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function blankValueProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'whitespace only' => [" \t\n"];
    }

    /**
     * @return iterable<string, array{Closure(string): AbstractNotEmptyString, string, string}>
     */
    public static function valueObjectNormalizationProvider(): iterable
    {
        foreach (self::valueObjectProvider() as $type => [$create]) {
            foreach (self::normalizationProvider() as $case => [$input, $expected]) {
                yield $type . ': ' . $case => [$create, $input, $expected];
            }
        }
    }

    /**
     * @return iterable<string, array{Closure(string): AbstractNotEmptyString, string}>
     */
    public static function valueObjectBlankValueProvider(): iterable
    {
        foreach (self::valueObjectProvider() as $type => [$create]) {
            foreach (self::blankValueProvider() as $case => [$value]) {
                yield $type . ': ' . $case => [$create, $value];
            }
        }
    }

    /**
     * @return iterable<string, array{Closure(string): AbstractNotEmptyString}>
     */
    public static function valueObjectProvider(): iterable
    {
        yield 'ConfigName' => [
            static fn(string $value): ConfigName => new ConfigName($value),
        ];

        yield 'SectionName' => [
            static fn(string $value): SectionName => new SectionName($value),
        ];

        yield 'BlockName' => [
            static fn(string $value): BlockName => new BlockName($value),
        ];

        yield 'ParameterName' => [
            static fn(string $value): ParameterName => new ParameterName($value),
        ];

        yield 'Alias' => [
            static fn(string $value): Alias => new Alias($value),
        ];
    }
}
