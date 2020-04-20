<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\Config;
use Ergnuor\SphinxConfig\Section;
use Ergnuor\SphinxConfig\Section\Type as SectionType;
use Ergnuor\SphinxConfig\Section\Writer\Adapter as WriterAdapter;
use \Ergnuor\SphinxConfig\Section\Reader\Adapter as ReaderAdapter;

class ConfigTest extends TestCase
{
    private $_readerAdapter;
    private $_writerAdapter;

    public function setUp()
    {
        $this->_readerAdapter = $this->getMockForAbstractClass(ReaderAdapter::class);
        $this->_writerAdapter = $this->getMockForAbstractClass(WriterAdapter::class);
    }

    public function testPlaceholderValues()
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

    private function getConfig()
    {
        return new Config(
            $this->_readerAdapter,
            $this->_writerAdapter
        );
    }

    public function testTransform()
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

    private function callbackSectionNameEquals($expectedSectionName) {
        return function(Section $section) use($expectedSectionName) {
            return $section->getName() == $expectedSectionName;
        };
    }
}
