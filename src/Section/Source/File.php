<?php

namespace Ergnuor\SphinxConfig\Section\Source;

use Ergnuor\SphinxConfig\Section\SourceAbstract;

class File extends SourceAbstract
{
    /**
     * Contents previously read configs
     *
     * @var array[]
     */
    static protected $singleFileConfigs = [];


    /**
     * The path to the directory containing the configs
     *
     * @var null|string
     */
    protected $configsRootPath = null;

    /**
     * @param string $configsRootPath
     */
    public function __construct($configsRootPath)
    {
        $this->configsRootPath = (string)$configsRootPath;
    }

    /**
     * Loads section config
     *
     * @param string $configName
     * @param string $sectionName
     * @return array
     */
    public function load($configName, $sectionName)
    {
        $sectionConfig = $this->loadFromMultiSectionFile($configName, $sectionName);
        $sectionConfig = array_replace(
            $sectionConfig,
            $this->loadFromSingleSectionFile($configName, $sectionName)
        );

        return $sectionConfig;
    }


    /**
     * Loads section blocks config stored in separate files
     *
     * @param string $configName
     * @param string $sectionName
     * @return array
     */
    public function loadBlocks($configName, $sectionName)
    {
        $blocks = [];

        $blockFilesPath = $this->configsRootPath . DIRECTORY_SEPARATOR . $configName . DIRECTORY_SEPARATOR . $sectionName . DIRECTORY_SEPARATOR;

        if (!is_dir($blockFilesPath)) {
            return [];
        }

        $blockFileList = scandir($blockFilesPath);

        foreach ($blockFileList as $blockFileName) {
            if (!is_file($blockFilesPath . $blockFileName)) {
                continue;
            }

            $blockName = preg_replace('/\.php$/', '', $blockFileName);
            $blockConfig = (array)include($blockFilesPath . $blockFileName);
            $blocks[$blockName] = $blockConfig;
        }

        return $blocks;
    }

    /**
     * Reads and caches config from a multi-sections file
     *
     * @param string $configName
     * @return array
     */
    protected function readMultiSectionFile($configName)
    {
        if (!isset(static::$singleFileConfigs[$configName])) {
            $singleFileName = $this->configsRootPath . DIRECTORY_SEPARATOR . $configName . '.php';

            if (!file_exists($singleFileName)) {
                static::$singleFileConfigs[$configName] = [];
            } else {
                static::$singleFileConfigs[$configName] = (array)include($singleFileName);
            }
        }

        return static::$singleFileConfigs[$configName];
    }


    /**
     * Loads a section config from a multi-sections file
     *
     * @param string $configName
     * @param string $sectionName
     * @return array
     */
    protected function loadFromMultiSectionFile($configName, $sectionName)
    {
        $config = $this->readMultiSectionFile($configName);
        return isset($config[$sectionName]) ? (array)$config[$sectionName] : [];
    }


    /**
     * Loads a section config from a single-section file
     *
     * @param string $configName
     * @param string $sectionName
     * @return array
     */
    protected function loadFromSingleSectionFile($configName, $sectionName)
    {
        $configRootPath = $this->configsRootPath . DIRECTORY_SEPARATOR . $configName;
        $sectionFileName = $configRootPath . DIRECTORY_SEPARATOR . $sectionName . '.php';
        if (!file_exists($sectionFileName)) {
            return [];
        }

        return (array)include($sectionFileName);
    }
}