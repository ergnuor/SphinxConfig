<?php

namespace Ergnuor\SphinxConfig\Section\Reader\Adapter;

use Ergnuor\SphinxConfig\Section;
use Ergnuor\SphinxConfig\Exception\ReaderException;
use Ergnuor\SphinxConfig\Section\Reader\Adapter;

abstract class File implements Adapter
{
    /**
     * Caches previously read configs
     *
     * @var array[]
     */
    protected $multiSectionConfigs = [];
    protected $extension = 'conf';
    /**
     * The path to the directory containing the configs
     *
     * @var string
     */
    private $configsRootPath = null;

    /**
     * @param string $configsRootPath
     * @throws ReaderException
     */
    public function __construct($configsRootPath)
    {
        $this->configsRootPath = trim((string)$configsRootPath);

        if (!is_dir($this->configsRootPath)) {
            throw new ReaderException("'{$this->configsRootPath}' is not a directory");
        }

        $this->configsRootPath = realpath($this->configsRootPath);
    }

    public function readConfig($configName, Section $section)
    {
        $sectionName = $section->getName();
        $sectionConfig = $this->readFromMultiSectionConfigCached($configName, $sectionName);
        $sectionConfig = array_replace(
            $sectionConfig,
            $this->readFromSingleSectionConfig($configName, $sectionName)
        );

        return $sectionConfig;
    }

    /**
     * @param string $configName
     * @param string $sectionName
     * @return array
     */
    private function readFromMultiSectionConfigCached($configName, $sectionName)
    {
        if (!isset($this->multiSectionConfigs[$configName])) {
            $this->multiSectionConfigs[$configName] =
                $this->readMultiSectionConfig($configName);
        }

        return isset($this->multiSectionConfigs[$configName][$sectionName]) ?
            (array)$this->multiSectionConfigs[$configName][$sectionName] : [];
    }

    /**
     * @param string $configName
     * @return array
     */
    private function readMultiSectionConfig($configName)
    {
        $singleFileName = $this->configsRootPath . DIRECTORY_SEPARATOR . $configName . '.' . $this->extension;

        if (!file_exists($singleFileName)) {
            return [];
        }
        return $this->readFile($singleFileName);
    }

    abstract protected function readFile($filePath);

    /**
     * @param string $configName
     * @param string $sectionName
     * @return array
     */
    private function readFromSingleSectionConfig($configName, $sectionName)
    {
        $configRootPath = $this->configsRootPath . DIRECTORY_SEPARATOR . $configName;
        $sectionFileName = $configRootPath . DIRECTORY_SEPARATOR . $sectionName . '.' . $this->extension;

        if (!file_exists($sectionFileName)) {
            return [];
        }

        return $this->readFile($sectionFileName);
    }

    /**
     * {@inheritdoc}
     */
    public function readConfigBlocks($configName, Section $section)
    {
        $sectionName = $section->getName();

        $blockFilesPath = $this->configsRootPath . DIRECTORY_SEPARATOR . $configName . DIRECTORY_SEPARATOR . $sectionName . DIRECTORY_SEPARATOR;

        if (!is_dir($blockFilesPath)) {
            return [];
        }

        $blockFileList = scandir($blockFilesPath);

        $blocks = [];
        foreach ($blockFileList as $blockFileName) {
            if (!is_file($blockFilesPath . $blockFileName)) {
                continue;
            }

            $blockConfig = $this->readFile($blockFilesPath . $blockFileName);
            $blockName = preg_replace('/\.' . preg_quote($this->extension) . '$/', '', $blockFileName);
            $blocks[$blockName] = $blockConfig;
        }

        return $blocks;
    }

    public function reset()
    {
        $this->multiSectionConfigs = [];
    }
}