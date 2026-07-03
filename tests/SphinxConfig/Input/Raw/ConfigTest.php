<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Input\Raw;

use Ergnuor\SphinxConfig\Domain\ConfigName;
use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;
use Ergnuor\SphinxConfig\Input\Raw\Config;
use Ergnuor\SphinxConfig\Input\Raw\Section;
use Generator;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testRejectsWrongSectionTypes(): void
    {
        $sections = [
            new Section('unknownSphinxSectionName', []),
            'wrongType',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Raw section must be instance of ' . Section::class);

        // @phpstan-ignore argument.type (Intentionally passing invalid element type to verify defensive runtime guard)
        $this->newConfigWithSections($sections);
    }

    public function testRejectsDuplicateSectionNames(): void
    {
        $duplicateSectionName = 'duplicateSectionName';
        $sections = [
            new Section('uniqueName', []),
            new Section($duplicateSectionName, []),
            new Section($duplicateSectionName, []),
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains("Duplicate section name '{$duplicateSectionName}'");

        $this->newConfigWithSections($sections);
    }

    public function testStoresSectionsIndexedBySectionName(): void
    {
        $sectionName1 = 'sectionName1';
        $sectionName2 = 'sectionName2';
        $sectionName3 = 'sectionName3';

        $sectionsByName = [
            $sectionName1 => new Section($sectionName1, []),
            $sectionName2 => new Section($sectionName2, []),
            $sectionName3 => new Section($sectionName3, []),
        ];

        $config = $this->newConfigWithSections(
            array_values($sectionsByName),
        );

        $this->assertSame($sectionsByName, $config->sections);
    }

    public function testAllowsEmptySectionList(): void
    {
        $config = $this->newConfigWithSections([]);

        $this->assertEmpty($config->sections);
    }

    public function testStoresConfigName(): void
    {
        $configName = new ConfigName('configName');

        $config = new Config(
            $configName,
            [],
        );

        $this->assertSame($configName, $config->name);
    }

    public function testAcceptsNonArrayIterable(): void
    {
        $sectionName = 'sectionName';
        $section = new Section($sectionName, []);

        $config = $this->newConfigWithSections(
            (function () use ($section): Generator {
                yield $section;
            })()
        );

        $this->assertSame(
            [
                $sectionName => $section,
            ],
            $config->sections,
        );
    }

    /**
     * @param iterable<Section> $sections
     */
    private function newConfigWithSections(iterable $sections): Config
    {
        return new Config(
            new ConfigName('config'),
            $sections,
        );
    }
}
