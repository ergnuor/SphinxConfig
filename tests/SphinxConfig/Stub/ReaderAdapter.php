<?php


namespace Ergnuor\SphinxConfig\Tests\Stub;


use Ergnuor\SphinxConfig\Section;
use Ergnuor\SphinxConfig\Section\Reader\Adapter;

class ReaderAdapter implements Adapter
{
    private $sourceData = [];

    public function readConfig($configName, Section $section)
    {
        if (!isset($this->sourceData[$configName])) {
            return [];
        }

        if (!isset($this->sourceData[$configName][$section->getName()])) {
            return [];
        }

        return $this->sourceData[$configName][$section->getName()];
    }

    /**
     * @inheritDoc
     */
    public function readConfigBlocks($configName, Section $section)
    {
        return [];
    }


    public function reset()
    {
        $this->sourceData = [];
    }

    /**
     * @param mixed $sourceData
     */
    public function setSourceData($sourceData)
    {
        $this->sourceData = $sourceData;
    }
}