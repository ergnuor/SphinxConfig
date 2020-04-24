<?php

namespace Ergnuor\SphinxConfig\Section\Reader;


use Ergnuor\SphinxConfig\Section;

interface Adapter
{
    /**
     * Method called before the formation of sections
     */
    public function reset(): void;

    public function readConfig(string $configName, Section $section): array;

    /**
     * Reads section blocks
     *
     * Allow you to store settings in separate blocks for sections like 'indexer', 'searchd' and 'common'
     * It may be useful for storing common parameters used by different configurations
     * @param string $configName
     * @param Section $section
     * @return array
     */
    public function readConfigBlocks(string $configName, Section $section): array;
}