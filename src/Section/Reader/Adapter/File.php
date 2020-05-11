<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section\Reader\Adapter;

use Ergnuor\SphinxConfig\Exception\ReaderException;
use Ergnuor\SphinxConfig\Section\{Context, Reader\Adapter};

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

    public function reset(): void
    {
        $this->multiSectionConfigs = [];
    }

    public function read(Context $context): array
    {
        $config = $this->readFromMultiSectionConfigCached($context);
        $config = array_replace(
            $config,
            $this->readFromSingleSectionConfig($context)
        );

        return $config;
    }

    private function readFromMultiSectionConfigCached(Context $context): array
    {
        $configName = $context->getConfigName();
        $sectionType = $context->getSectionType();

        if (!isset($this->multiSectionConfigs[$configName])) {
            $this->multiSectionConfigs[$configName] =
                $this->readMultiSectionConfig($configName);
        }

        return $this->multiSectionConfigs[$configName][$sectionType] ?? [];
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

    private function readFromSingleSectionConfig(Context $sectionNContext): array
    {
        $configName = $sectionNContext->getConfigName();
        $sectionType = $sectionNContext->getSectionType();

        $configRootPath = $this->configsRootPath . DIRECTORY_SEPARATOR . $configName;
        $sectionFileName = $configRootPath . DIRECTORY_SEPARATOR . $sectionType . '.' . $this->extension;

        if (!file_exists($sectionFileName)) {
            return [];
        }

        return $this->readFile($sectionFileName);
    }

    /**
     * {@inheritdoc}
     */
    public function readSeparateBlocks(Context $context): array
    {
        $configName = $context->getConfigName();
        $sectionType = $context->getSectionType();

        $blockFilesPath = $this->configsRootPath . DIRECTORY_SEPARATOR . $configName
            . DIRECTORY_SEPARATOR . $sectionType . DIRECTORY_SEPARATOR;

        if (!is_dir($blockFilesPath)) {
            return [];
        }

        $blockFileList = scandir($blockFilesPath);

        $blocks = [];
        foreach ($blockFileList as $blockFileName) {
            if (!is_file($blockFilesPath . $blockFileName)) {
                continue;
            }

            $block = $this->readFile($blockFilesPath . $blockFileName);
            $blockName = preg_replace('/\.' . preg_quote($this->extension) . '$/', '', $blockFileName);
            $blocks[$blockName] = $block;
        }

        return $blocks;
    }
}