<?php

namespace Ergnuor\SphinxConfig\Section\Reader\Adapter;

use Ergnuor\SphinxConfig\{
    Section,
    Exception\ReaderException,
    Section\Reader\Adapter
};

abstract class File implements Adapter
{
    protected $extension = 'conf';

    /**
     * Caches previously read configs
     *
     * @var array[]
     */
    private $multiSectionConfigs = [];

    /**
     * @var string
     */
    private $configsRootPath;

    /**
     * @param string $configsRootPath
     * @throws ReaderException
     */
    public function __construct(string $configsRootPath)
    {
        $this->configsRootPath = trim($configsRootPath);

        if (!is_dir($this->configsRootPath)) {
            throw new ReaderException("'{$this->configsRootPath}' is not a directory");
        }

        $this->configsRootPath = realpath($this->configsRootPath);
    }

    public function readConfig(string $configName, Section $section): array
    {
        $sectionName = $section->getName();
        $sectionConfig = $this->readFromMultiSectionConfigCached($configName, $sectionName);
        $sectionConfig = array_replace(
            $sectionConfig,
            $this->readFromSingleSectionConfig($configName, $sectionName)
        );

        return $sectionConfig;
    }

    private function readFromMultiSectionConfigCached(string $configName, string $sectionName): array
    {
        if (!isset($this->multiSectionConfigs[$configName])) {
            $this->multiSectionConfigs[$configName] =
                $this->readMultiSectionConfig($configName);
        }

        return (array)($this->multiSectionConfigs[$configName][$sectionName] ?? []);
    }

    private function readMultiSectionConfig(string $configName): array
    {
        $singleFileName = $this->configsRootPath . DIRECTORY_SEPARATOR . $configName . '.' . $this->extension;

        if (!file_exists($singleFileName)) {
            return [];
        }
        return $this->readFile($singleFileName);
    }

    abstract protected function readFile(string $filePath): array;

    private function readFromSingleSectionConfig(string $configName, string $sectionName): array
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
    public function readConfigBlocks(string $configName, Section $section): array
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

    public function reset(): void
    {
        $this->multiSectionConfigs = [];
    }
}