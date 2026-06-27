<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\TestDouble\Stub;

use Ergnuor\SphinxConfig\{Section\Context, Section\Reader\Adapter};

class ReaderAdapter implements Adapter
{
    /**
     * @var array
     */
    private $sourceData = [];

    public function read(Context $context): array
    {
        $configName = $context->getConfigName();
        $sectionType = $context->getSectionType();
        if (!isset($this->sourceData[$configName])) {
            return [];
        }

        if (!isset($this->sourceData[$configName][$sectionType])) {
            return [];
        }

        return $this->sourceData[$configName][$sectionType];
    }

    /**
     * @inheritDoc
     */
    public function readSeparateBlocks(Context $context): array
    {
        return [];
    }


    public function reset(): void
    {
//        $this->sourceData = [];
    }

    public function setSourceData(array $sourceData): void
    {
        $this->sourceData = $sourceData;
    }
}