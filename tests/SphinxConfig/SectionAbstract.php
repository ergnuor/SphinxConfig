<?php
namespace Ergnuor\SphinxConfig\Tests;

use \Ergnuor\SphinxConfig\Section\SourceInterface;

abstract class SectionAbstract extends TestCase
{
    /**
     * @dataProvider configProvider
     */
    public function testParameters($sourceLoad, $sourceLoadBlocks, $expected, $globalPlaceholders)
    {
        $mockSource = $this->getMockBuilder(SourceInterface::class)
            ->getMock();

        $mockSource->expects($this->any())
            ->method('load')
            ->will($this->returnCallback(function ($configName, $sectionName) use ($sourceLoad) {
                return isset($sourceLoad[$configName]) ? $sourceLoad[$configName] : [];
            }));

        $mockSource->expects($this->any())
            ->method('loadBlocks')
            ->will($this->returnCallback(function ($configName, $sectionName) use ($sourceLoadBlocks) {
                return isset($sourceLoadBlocks[$configName]) ? $sourceLoadBlocks[$configName] : [];
            }));

        $sectionClass = $this->getSectionClass(
            $mockSource,
            $globalPlaceholders
        );

        $this->assertSame(
            $expected,
            $sectionClass->getConfig()
        );

//        echo var_export($sectionClass->getConfig(), true);
//        exit;
    }

    /**
     * @param SourceInterface $source
     * @param array $globalPlaceholderValues
     * @return \Ergnuor\SphinxConfig\Section\MultiBlock
     */
    abstract protected function getSectionClass(
        SourceInterface $source,
        array $globalPlaceholderValues
    );

    abstract public function configProvider();
}