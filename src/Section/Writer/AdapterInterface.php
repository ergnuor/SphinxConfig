<?php

namespace Ergnuor\SphinxConfig\Section\Writer;


interface AdapterInterface
{
    /**
     * Method called before the formation of sections
     *
     * @param string $configName
     */
    public function beforeWriteConfig($configName);

    /**
     * Method called after the formation of sections
     *
     * @param string $configName
     */
    public function afterWriteConfig($configName);

    /**
     * @param string $blockName
     * @param null|string $extends
     * @param null|string $sectionName
     */
    public function startBlock($blockName, $extends = null, $sectionName = null);

    public function endBlock();

    /**
     * @param string $paramName
     * @param string $paramValue
     */
    public function writeParam($paramName, $paramValue);
}