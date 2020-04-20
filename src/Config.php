<?php

namespace Ergnuor\SphinxConfig;

class Config
{
    /**
     * @var array
     */
    private $placeholderValues = [];

    /**
     * @var Section\Reader\Adapter
     */
    private $sectionReaderAdapter;

    /**
     * @var Section\Writer\Adapter
     */
    private $sectionWriterAdapter;

    /**
     * @param Section\Reader\Adapter $sectionReaderAdapter
     * @param Section\Writer\Adapter $sectionWriterAdapter
     */
    public function __construct(
        Section\Reader\Adapter $sectionReaderAdapter,
        Section\Writer\Adapter $sectionWriterAdapter
    )
    {
        $this->sectionReaderAdapter = $sectionReaderAdapter;
        $this->sectionWriterAdapter = $sectionWriterAdapter;
    }

    public static function phpArrayToNativeConfigStdout($srcPath)
    {
        return new static(
            new Section\Reader\Adapter\File\PhpArray($srcPath),
            new Section\Writer\Adapter\NativeConfig()
        );
    }

    public static function phpArrayToNativeConfigFile($srcPath, $dstPath)
    {
        return new static(
            new Section\Reader\Adapter\File\PhpArray($srcPath),
            new Section\Writer\Adapter\NativeConfig($dstPath)
        );
    }

    /**
     * @param string $configName
     * @return $this
     */
    public function transform($configName)
    {
        $sections = Section\Type::getTypes();

        $this->sectionWriterAdapter->reset();
        $this->sectionReaderAdapter->reset();

        foreach ($sections as $sectionName) {
            $section = new Section(
                $configName,
                $sectionName,
                $this->sectionReaderAdapter,
                $this->sectionWriterAdapter,
                $this->getPlaceholderValues()
            );
            $section->transform();
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getPlaceholderValues()
    {
        return $this->placeholderValues;
    }

    /**
     * @param array $placeholderValues
     * @return Config
     */
    public function setPlaceholderValues(array $placeholderValues)
    {
        $this->placeholderValues = $placeholderValues;
        return $this;
    }
}