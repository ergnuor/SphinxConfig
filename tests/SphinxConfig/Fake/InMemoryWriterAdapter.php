<?php


namespace Ergnuor\SphinxConfig\Tests\Fake;


use Ergnuor\SphinxConfig\Section\Writer\Adapter;

class InMemoryWriterAdapter implements Adapter
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * @var string
     */
    private $currentSectionName;

    /**
     * @var string
     */
    private $currentBlockName;

    public function reset(): void
    {
        $this->config = [];
        $this->resetCurrentSectionAndBlockName();
    }

    private function resetCurrentSectionAndBlockName(): void
    {
        $this->currentSectionName = null;
        $this->currentBlockName = null;
    }

    public function write(string $configName): void
    {
    }

    /**
     * @inheritDoc
     */
    public function startMultiBlockSection(
        string $sectionName,
        string $blockName,
        string $extends = null
    ): void
    {
        $this->config[$sectionName] = $this->config[$sectionName] ?? [];
        $this->config[$sectionName][$blockName] = [];

        if (!is_null($extends)) {
            $this->config[$sectionName][$blockName]['extends'] = $extends;
        }

        $this->currentSectionName = $sectionName;
        $this->currentBlockName = $blockName;
    }

    public function endMultiBlockSection(): void
    {
        $this->resetCurrentSectionAndBlockName();
    }

    /**
     * @inheritDoc
     */
    public function startSingleBlockSection(string $sectionName): void
    {
        $this->config[$sectionName] = $this->config[$sectionName] ?: [];
        $this->currentSectionName = $sectionName;
        $this->currentBlockName = null;
    }

    public function endSingleBlockSection(): void
    {
        $this->resetCurrentSectionAndBlockName();
    }

    /**
     * @inheritDoc
     */
    public function writeParam(string $paramName, string $paramValue): void
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

    public function getConfig(): array
    {
        return $this->config;
    }
}