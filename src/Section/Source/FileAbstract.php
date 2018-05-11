<?php

namespace Ergnuor\SphinxConfig\Section\Source;

use Ergnuor\SphinxConfig\Section\SourceInterface;
use Ergnuor\SphinxConfig\Exception\SourceException;

abstract class FileAbstract implements SourceInterface
{
    protected $extension = null;

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
     * @throws SourceException
     */
    public function __construct($configsRootPath)
    {
        $this->configsRootPath = trim((string)$configsRootPath);

        if (empty($this->configsRootPath)) {
            throw new SourceException("Source path required");
        }

        if (!file_exists($this->configsRootPath)) {
            throw new SourceException("Source directory '{$this->configsRootPath}' does not exists");
        }
    }

    /**
     * {@inheritdoc}
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

            $blockName = preg_replace('/\.' . preg_quote($this->extension) . '$/', '', $blockFileName);
            $blockConfig = $this->readFile($blockFilesPath . $blockFileName);
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
            $singleFileName = $this->configsRootPath . DIRECTORY_SEPARATOR . $configName . '.' . $this->extension;

            if (!file_exists($singleFileName)) {
                static::$singleFileConfigs[$configName] = [];
            } else {
                static::$singleFileConfigs[$configName] = $this->readFile($singleFileName);
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
        $sectionFileName = $configRootPath . DIRECTORY_SEPARATOR . $sectionName . '.' . $this->extension;
        if (!file_exists($sectionFileName)) {
            return [];
        }

        return $this->readFile($sectionFileName);
    }

    public function beforeReadSections()
    {
        static::$singleFileConfigs = [];
    }

    public function afterReadSections()
    {

    }

    abstract protected function readFile($filePath);
}