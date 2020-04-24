<?php

namespace Ergnuor\SphinxConfig\Section\Writer;


interface Adapter
{
    /**
     * Method called before the formation of sections
     */
    public function reset(): void;

    public function write(string $configName): void;

    public function startMultiBlockSection(
        string $sectionName,
        string $blockName,
        string $extends = null
    ): void;

    public function endMultiBlockSection(): void;

    public function startSingleBlockSection(string $sectionName): void;

    public function endSingleBlockSection(): void;

    public function writeParam(string $paramName, string $paramValue): void;
}