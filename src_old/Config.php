<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfigOld;

use Ergnuor\SphinxConfigOld\Section\Context;
use Ergnuor\SphinxConfigOld\Section\Utility\Type as SectionType;
use Ergnuor\SphinxConfigOld\Section\SourceConfig\Assembler;
use Ergnuor\SphinxConfigOld\Section\Processor\{
    Cleaner as CleanerProcessor,
    Inheritance as InheritanceProcessor,
    Parameter as ParameterProcessor
};
use Ergnuor\SphinxConfigOld\Section\Reader;
use Ergnuor\SphinxConfigOld\Section\Reader\Adapter as ReaderAdapter;
use Ergnuor\SphinxConfigOld\Section\Reader\Adapter\File\PhpArray as PhpArrayReaderAdapter;
use Ergnuor\SphinxConfigOld\Section\Writer;
use Ergnuor\SphinxConfigOld\Section\Writer\{Adapter as WriterAdapter, Adapter\NativeConfig as NativeConfigWriterAdapter};

class Config
{
    /**
     * @var array
     */
    private $placeholderValues = [];

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var Writer
     */
    private $writer;

    /**
     * Config constructor.
     * @param ReaderAdapter $sectionReaderAdapter
     * @param WriterAdapter $sectionWriterAdapter
     */
    public function __construct(ReaderAdapter $sectionReaderAdapter, WriterAdapter $sectionWriterAdapter)
    {
        $this->reader = new Reader($sectionReaderAdapter);
        $this->writer = new Writer($sectionWriterAdapter);
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
     * @throws Exception\SectionException
     * @throws Exception\WriterException
     */
    public function transform(string $configName): Config
    {
        $this->reader->reset();
        $this->writer->reset();

        $assembler = new Assembler($this->reader);

        foreach (SectionType::getTypes() as $sectionType) {
            $context = new Context($configName, $sectionType);

            $config = $assembler->assemble($context);

            $config = $this->process($context, $config);

            $this->writer->write($config, $context);
        }

        return $this;
    }

    /**
     * @param Context $context
     * @param array $config
     * @return array
     * @throws Exception\SectionException
     */
    private function process(Context $context, array $config): array
    {
        $config = InheritanceProcessor::process($context, $config);
        $config = ParameterProcessor::process($context, $config, $this->getPlaceholderValues());
        $config = CleanerProcessor::process($config);

        return $config;
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