<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\TestDouble\Fake;

use Ergnuor\SphinxConfig\Section\{Context, Writer\Adapter};

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
//        $this->config = [];
        $this->resetCurrentSectionAndBlockName();
    }

    private function resetCurrentSectionAndBlockName(): void
    {
        $this->currentSectionName = null;
        $this->currentBlockName = null;
    }

    public function write(Context $context): void
    {
    }

    /**
     * @inheritDoc
     */
    public function startMultiBlockSection(
        string $sectionType,
        string $blockName,
        string $extends = null
    ): void
    {
        $this->config[$sectionType] = $this->config[$sectionType] ?? [];
        $this->config[$sectionType][$blockName] = [];

        if (!is_null($extends)) {
            $this->config[$sectionType][$blockName]['extends'] = $extends;
        }

        $this->currentSectionName = $sectionType;
        $this->currentBlockName = $blockName;
    }

    public function endMultiBlockSection(): void
    {
        $this->resetCurrentSectionAndBlockName();
    }

    /**
     * @inheritDoc
     */
    public function startSingleBlockSection(string $sectionType): void
    {
        $this->config[$sectionType] = $this->config[$sectionType] ?? [];
        $this->currentSectionName = $sectionType;
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