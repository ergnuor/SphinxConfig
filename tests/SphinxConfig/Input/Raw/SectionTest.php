<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Input\Raw;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;
use Ergnuor\SphinxConfig\Input\Raw\Section;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SectionTest extends TestCase
{
    public function testTrimsName(): void
    {
        $section = new Section('   sectionName    ', []);

        $this->assertSame('sectionName', $section->name);
    }

    public function testAcceptsUnknownSphinxSectionName(): void
    {
        $section = new Section('unknownSphinxSectionName', []);
        $this->assertSame('unknownSphinxSectionName', $section->name);
    }

    #[DataProvider('blankNameProvider')]
    public function testRejectsBlankName(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Section($name, []);
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

    public function testPreservesRawData(): void
    {
        $data = [
            'someKey' => [
                'someInnerKey' => 'someInnerValue',
            ],
        ];

        $section = new Section('sectionName', $data);

        $this->assertArraysAreIdentical($data, $section->data);
    }
}
