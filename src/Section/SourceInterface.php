<?php

namespace Ergnuor\SphinxConfig\Section;


interface SourceInterface
{
    /**
     * Loads section config
     *
     * The structure of the returned array depends on the type of section being loaded.
     * The 'source' and 'index' sections must be returned as:
     * [
     *      'blockName_1' => [
     *          // block parameters here
     *      ],
     *      ...
     *
     *      'blockName_N' => [
     *          // block parameters here
     *      ]
     * ];
     *
     * The 'indexer', 'searchd' and 'common' sections must be returned as:
     * [
     *      // block parameters here
     * ];
     *
     * @param string $configName
     * @param string $sectionName
     * @return array
     */
    public function load($configName, $sectionName);

    /**
     * Loads section blocks
     *
     * Complements the \Ergnuor\SphinxConfig\Section\SourceInterface::load method.
     * Allow you to store settings in separate blocks for sections like 'indexer', 'searchd' and 'common'
     * It may be useful for storing common parameters used by different configurations
     *
     * @param string $configName
     * @param string $sectionName
     * @return array
     */
    function loadBlocks($configName, $sectionName);
}