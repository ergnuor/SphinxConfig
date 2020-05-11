<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section\Writer;

use Ergnuor\SphinxConfig\Section\Context;

interface Adapter
{
    /**
     * Method called before the formation of sections
     */
    public function reset(): void;

    /**
     * @param Context $context
     */
    public function write(Context $context): void;

    public function startMultiBlockSection(
        string $sectionType,
        string $blockName,
        string $extends = null
    ): void;

    public function endMultiBlockSection(): void;

    public function startSingleBlockSection(string $sectionType): void;

    public function endSingleBlockSection(): void;

    public function writeParam(string $paramName, string $paramValue): void;
}