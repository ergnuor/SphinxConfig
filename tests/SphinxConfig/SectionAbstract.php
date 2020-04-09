<?php

namespace Ergnuor\SphinxConfig\Tests;

use \Ergnuor\SphinxConfig\Section\SourceInterface;

abstract class SectionAbstract extends TestCase
{
    /**
     * @dataProvider configProvider
     */
    public function testConfigTransformations(
        $sourceForLoadMethod,
        $sourceForLoadBlocksMethod,
        $expected,
        $globalPlaceholders
    )
    {
        $sourceMock = $this->getConfigSourceMock($sourceForLoadMethod, $sourceForLoadBlocksMethod);

        $this->assertSame(
            $expected,
            $this->getSectionConfig(
                $sourceMock,
                $globalPlaceholders
            )
        );
    }

    protected function getSectionConfig(
        SourceInterface $source,
        array $globalPlaceholderValues
    )
    {
        $sectionClass = $this->getSectionClass(
            $source,
            $globalPlaceholderValues
        );

        return $sectionClass->getConfig();
    }

    /**
     * @param $sourceForLoadMethod
     * @param $sourceForLoadBlocksMethod
     * @return \Ergnuor\SphinxConfig\Section\SourceInterface
     */
    protected function getConfigSourceMock($sourceForLoadMethod, $sourceForLoadBlocksMethod)
    {
        return new Mock\Source(
            $sourceForLoadMethod,
            $sourceForLoadBlocksMethod
        );
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