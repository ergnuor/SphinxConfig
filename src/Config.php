<?php

namespace Ergnuor\SphinxConfig;

use Ergnuor\SphinxConfig\Section\Reader\{
    Adapter as ReaderAdapter,
    Adapter\File\PhpArray as PhpArrayReaderAdapter
};
use Ergnuor\SphinxConfig\Section\Writer\{
    Adapter as WriterAdapter,
    Adapter\NativeConfig as NativeConfigWriterAdapter
};

class Config
{
    /**
     * @var array
     */
    private $placeholderValues = [];

    /**
     * @var ReaderAdapter
     */
    private $sectionReaderAdapter;

    /**
     * @var WriterAdapter
     */
    private $sectionWriterAdapter;

    public function __construct(
        ReaderAdapter $sectionReaderAdapter,
        WriterAdapter $sectionWriterAdapter
    )
    {
        $this->sectionReaderAdapter = $sectionReaderAdapter;
        $this->sectionWriterAdapter = $sectionWriterAdapter;
    }

    /**
     * @param string $srcPath
     * @return Config
     * @throws Exception\ReaderException
     */
    public static function phpArrayToNativeConfigStdout(string $srcPath): Config
    {
        return new static(
            new PhpArrayReaderAdapter($srcPath),
            new NativeConfigWriterAdapter()
        );
    }

    /**
     * @param string $srcPath
     * @param string $dstPath
     * @return Config
     * @throws Exception\ReaderException
     */
    public static function phpArrayToNativeConfigFile(string $srcPath, string $dstPath): Config
    {
        return new static(
            new PhpArrayReaderAdapter($srcPath),
            new NativeConfigWriterAdapter($dstPath)
        );
    }

    /**
     * @param string $configName
     * @return Config
     * @throws Exception\WriterException
     * @throws Exception\SectionException
     */
    public function transform(string $configName): Config
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

    public function getPlaceholderValues(): array
    {
        return $this->placeholderValues;
    }

    public function setPlaceholderValues(array $placeholderValues): Config
    {
        $this->placeholderValues = $placeholderValues;
        return $this;
    }
}