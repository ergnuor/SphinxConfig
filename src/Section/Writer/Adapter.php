<?php

namespace Ergnuor\SphinxConfig\Section\Writer;


interface Adapter
{
    /**
     * Method called before the formation of sections
     */
    public function reset();

    public function write($configName);

    /**
     * @param string $sectionName
     * @param string $blockName
     * @param null|string $extends
     */
    public function startMultiBlockSection($sectionName, $blockName, $extends = null);

    public function endMultiBlockSection();

    /**
     * @param string $sectionName
     */
    public function startSingleBlockSection($sectionName);

    public function endSingleBlockSection();

    /**
     * @param string $paramName
     * @param string $paramValue
     */
    public function writeParam($paramName, $paramValue);
}