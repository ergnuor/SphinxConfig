<?php

namespace Ergnuor\SphinxConfig\Section;


abstract class SourceAbstract implements SourceInterface
{
    /**
     * Loads section config
     *
     * @param string $configName
     * @param string $sectionName
     * @return array
     */
    abstract public function load($configName, $sectionName);

    /**
     * Loads section blocks
     *
     * @param string $configName
     * @param string $sectionName
     * @return array
     */
    public function loadBlocks($configName, $sectionName)
    {
        return [];
    }
}