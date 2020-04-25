<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\{
    Config,
    Section,
    Section\Reader\Adapter as ReaderAdapter,
    Section\Type as SectionType,
    Section\Writer\Adapter as WriterAdapter,
    Tests\TestCase\TestCase
};
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @uses \Ergnuor\SphinxConfig\Section\Type
 * @uses \Ergnuor\SphinxConfig\Section
 * @uses \Ergnuor\SphinxConfig\Section\Reader
 * @uses \Ergnuor\SphinxConfig\Section\Writer
 */
class ConfigTest extends TestCase
{
    /**
     * @var ReaderAdapter|MockObject
     */
    private $_readerAdapter;

    /**
     * @var WriterAdapter|MockObject
     */
    private $_writerAdapter;

    public function setUp(): void
    {
        $this->_readerAdapter = $this->getMockForAbstractClass(ReaderAdapter::class);
        $this->_writerAdapter = $this->getMockForAbstractClass(WriterAdapter::class);
    }

    public function testPlaceholderValues(): void
    {
        $config = $this->getConfig();

        $placeholderValues = [
            'some' => [
                'placeholder' => 'value',
            ],
        ];

        $config->setPlaceholderValues($placeholderValues);

        $this->assertSame(
            $placeholderValues,
            $config->getPlaceholderValues()
        );
    }

    private function getConfig(): Config
    {
        return new Config(
            $this->_readerAdapter,
            $this->_writerAdapter
        );
    }

    public function testTransform(): void
    {
        $configName = 'configName';

        $config = $this->getConfig();

        $this->_writerAdapter->expects($this->once())
            ->method('reset');

        $this->_readerAdapter->expects($this->once())
            ->method('reset');

        $this->_readerAdapter->expects($this->exactly(5))
            ->method('readConfig')
            ->withConsecutive(
                [$configName, $this->callback($this->callbackSectionNameEquals(SectionType::SOURCE))],
                [$configName, $this->callback($this->callbackSectionNameEquals(SectionType::INDEX))],
                [$configName, $this->callback($this->callbackSectionNameEquals(SectionType::INDEXER))],
                [$configName, $this->callback($this->callbackSectionNameEquals(SectionType::SEARCHD))],
                [$configName, $this->callback($this->callbackSectionNameEquals(SectionType::COMMON))]
            )
            ->willReturn([]);

        $this->_readerAdapter->expects($this->any())
            ->method('readConfigBlocks')
            ->willReturn([]);

        $config->transform($configName);
    }

    private function callbackSectionNameEquals(string $expectedSectionName): callable
    {
        return function (Section $section) use ($expectedSectionName): bool {
            return $section->getName() == $expectedSectionName;
        };
    }
}
