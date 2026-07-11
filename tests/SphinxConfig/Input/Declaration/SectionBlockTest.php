<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Input\Declaration;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;
use Ergnuor\SphinxConfig\Input\Declaration\Parameter;
use Ergnuor\SphinxConfig\Input\Declaration\SectionBlock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SectionBlockTest extends TestCase
{
    public function testTrimsName(): void
    {
        $section = new SectionBlock('   sectionName    ', 'blockName', []);

        $this->assertSame('sectionName', $section->sectionName);
    }

    public function testAcceptsUnknownSphinxSectionName(): void
    {
        $section = new SectionBlock('unknownSphinxSectionName', 'blockName', []);
        $this->assertSame('unknownSphinxSectionName', $section->sectionName);
    }

    #[DataProvider('blankNameProvider')]
    public function testRejectsBlankName(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SectionBlock($name, 'blockName', []);
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

    public function testPreservesParameters(): void
    {
        $parameters = [
            new Parameter('someParameterName', 'someParameterValue'),
        ];

        $section = new SectionBlock('sectionName', 'blockName', $parameters);

        $this->assertArraysAreIdentical($parameters, $section->parameters);
    }
}
