<?php


namespace Ergnuor\SphinxConfig\Tests\Fake;


use Ergnuor\SphinxConfig\Section\Writer\Adapter;

class InMemoryWriterAdapter implements Adapter
{
    private $config = [];

    private $currentSectionName;
    private $currentBlockName;

    public function reset()
    {
        $this->config = [];
        $this->resetCurrentSectionAndBlockName();
    }

    private function resetCurrentSectionAndBlockName()
    {
        $this->currentSectionName = null;
        $this->currentBlockName = null;
    }

    public function write($configName)
    {
    }

    /**
     * @inheritDoc
     */
    public function startMultiBlockSection($sectionName, $blockName, $extends = null)
    {
        if (!isset($this->config[$sectionName])) {
            $this->config[$sectionName] = [];
        }

        $this->config[$sectionName][$blockName] = [];

        if (!is_null($extends)) {
            $this->config[$sectionName][$blockName]['extends'] = $extends;
        }

        $this->currentSectionName = $sectionName;
        $this->currentBlockName = $blockName;
    }

    public function endMultiBlockSection()
    {
        $this->resetCurrentSectionAndBlockName();
    }

    /**
     * @inheritDoc
     */
    public function startSingleBlockSection($sectionName)
    {
        $this->config[$sectionName] = $this->config[$sectionName] ?: [];
        $this->currentSectionName = $sectionName;
        $this->currentBlockName = null;
    }

    public function endSingleBlockSection()
    {
        $this->resetCurrentSectionAndBlockName();
    }

    /**
     * @inheritDoc
     */
    public function writeParam($paramName, $paramValue)
    {
        $paramRoot = &$this->config[$this->currentSectionName];
        if (!is_null($this->currentBlockName)) {
            $paramRoot = &$paramRoot[$this->currentBlockName];
        }

        if (isset($paramRoot[$paramName])) {
            if (!is_array($paramRoot[$paramName])) {
                $paramRoot[$paramName] = [
                    $paramRoot[$paramName]
                ];
            }

            $paramRoot[$paramName][] = $paramValue;
        } else {
            $paramRoot[$paramName] = $paramValue;
        }
    }

    public function getConfig()
    {
        return $this->config;
    }
}