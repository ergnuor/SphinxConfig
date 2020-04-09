<?php

namespace Ergnuor\SphinxConfig\Tests\Mock;

class Source implements \Ergnuor\SphinxConfig\Section\SourceInterface
{
    private $sourceForLoadMethod;
    private $sourceForLoadBlocksMethod;

    public function __construct($sourceForLoadMethod, $sourceForLoadBlocksMethod)
    {
        $this->sourceForLoadMethod = $sourceForLoadMethod;
        $this->sourceForLoadBlocksMethod = $sourceForLoadBlocksMethod;
    }

    /**
     * @inheritDoc
     */
    public function load($configName, $sectionName)
    {
        return isset($this->sourceForLoadMethod[$configName]) ?
            $this->sourceForLoadMethod[$configName] : [];
    }

    /**
     * @inheritDoc
     */
    function loadBlocks($configName, $sectionName)
    {
        return isset($this->sourceForLoadBlocksMethod[$configName]) ?
            $this->sourceForLoadBlocksMethod[$configName] : [];
    }

    function beforeReadSections()
    {
    }

    function afterReadSections()
    {
    }
}