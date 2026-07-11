<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Input\Declaration;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;
use Ergnuor\SphinxConfig\Input\Declaration\Config;
use Ergnuor\SphinxConfig\Input\Declaration\SectionBlock;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testRejectsWrongSectionBlockTypes(): void
    {
        $sectionBlocks = [
            new SectionBlock('unknownSphinxSectionName', 'blockName', []),
            'wrongType',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains("Section block must be an instance of '" . SectionBlock::class . "'.");

        // @phpstan-ignore argument.type (Intentionally passing invalid element type to verify defensive runtime guard)
        $config = new Config($sectionBlocks);
    }

    public function testStoresSectionBlocks(): void
    {
        $sectionBlocks = [
            new SectionBlock('sectionName1', 'blockName1', []),
            new SectionBlock('sectionName2', 'blockName2', []),
            new SectionBlock('sectionName3', 'blockName3', []),
        ];

        $config = new Config($sectionBlocks);

        $this->assertSame($sectionBlocks, $config->sectionBlocks);
    }

    public function testAllowsEmptySectionBlockList(): void
    {
        $config = new Config([]);

        $this->assertEmpty($config->sectionBlocks);
    }
}
