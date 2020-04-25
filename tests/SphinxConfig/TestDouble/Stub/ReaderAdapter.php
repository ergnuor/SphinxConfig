<?php


namespace Ergnuor\SphinxConfig\Tests\TestDouble\Stub;


use Ergnuor\SphinxConfig\{
    Section,
    Section\Reader\Adapter
};

class ReaderAdapter implements Adapter
{
    /**
     * @var array
     */
    private $sourceData = [];

    public function readConfig(string $configName, Section $section): array
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
    public function readConfigBlocks(string $configName, Section $section): array
    {
        return [];
    }


    public function reset(): void
    {
        $this->sourceData = [];
    }

    public function setSourceData(array $sourceData): void
    {
        $this->sourceData = $sourceData;
    }
}