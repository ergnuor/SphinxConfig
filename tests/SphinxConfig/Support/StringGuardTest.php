<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Support;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;
use Ergnuor\SphinxConfig\Support\StringGuard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StringGuardTest extends TestCase
{
    #[DataProvider('valueWithOuterWhitespaceProvider')]
    public function testTrimsOuterWhitespace(string $input, string $expected): void
    {
        $this->assertSame(
            $expected,
            StringGuard::requireTrimmedNotEmpty($input, 'Value cannot be empty.'),
        );
    }

    #[DataProvider('nonBlankValueProvider')]
    public function testPreservesNonBlankValue(string $value): void
    {
        $this->assertSame(
            $value,
            StringGuard::requireTrimmedNotEmpty($value, 'Value cannot be empty.'),
        );
    }

    #[DataProvider('blankValueProvider')]
    public function testRejectsBlankValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        StringGuard::requireTrimmedNotEmpty($value, 'Value cannot be empty.');
    }

    public function testPreservesProvidedErrorMessage(): void
    {
        $message = 'Custom empty value message.';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs($message);

        StringGuard::requireTrimmedNotEmpty('', $message);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function valueWithOuterWhitespaceProvider(): iterable
    {
        yield 'spaces' => ['  Name  ', 'Name'];
        yield 'tabs' => ["\tOther\t", 'Other'];
        yield 'line feeds' => ["\nLineFeedName\n", 'LineFeedName'];
        yield 'carriage returns' => ["\rCarriageReturnName\r", 'CarriageReturnName'];
        yield 'null bytes' => ["\0NullByteName\0", 'NullByteName'];
        yield 'vertical tabs' => ["\x0BVerticalTabName\x0B", 'VerticalTabName'];
        yield 'all trim characters' => [" \t\n\r\0\x0BMiXeD  Name \t\n\r\0\x0B", 'MiXeD  Name'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function nonBlankValueProvider(): iterable
    {
        yield 'unchanged string' => ['name'];
        yield 'mixed case' => ['MiXeD'];
        yield 'inner spaces' => ['Name  With  Spaces'];
        yield 'inner trim characters' => ["Name \t\n\r\0\x0BWithWhitespace"];
        yield 'zero' => ['0'];
        yield 'punctuation' => ['@:/\\.*_-[]{}'];
        yield 'non-breaking space' => ["\u{00A0}"];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function blankValueProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'spaces' => ['   '];
        yield 'tabs' => ["\t"];
        yield 'line feeds' => ["\n"];
        yield 'carriage returns' => ["\r"];
        yield 'null bytes' => ["\0"];
        yield 'vertical tabs' => ["\x0B"];
        yield 'all trim characters' => [" \t\n\r\0\x0B"];
    }
}
