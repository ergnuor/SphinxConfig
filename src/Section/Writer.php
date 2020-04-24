<?php

namespace Ergnuor\SphinxConfig\Section;

use Ergnuor\SphinxConfig\{
    Exception\WriterException,
    Section,
    Section\Writer\Adapter as WriterAdapter
};

class Writer
{
    /**
     * @var WriterAdapter
     */
    private $writerAdapter;

    public function __construct(WriterAdapter $writerAdapter)
    {
        $this->writerAdapter = $writerAdapter;
    }

    /**
     * @param array $config
     * @param Section $section
     * @throws WriterException
     */
    final public function write(array $config, Section $section): void
    {
        $sectionName = $section->getName();

        if (Type::isSingleBlock($sectionName)) {
            $this->writeSingleBlockSection($config, $section);
        } else if (Type::isMultiBlock($sectionName)) {
            $this->writeMultiBlockSection($config, $section);
        } else {
            throw new WriterException("Unknown section type '{$sectionName}'");
        }

        $this->writerAdapter->write($section->getConfigName());
    }

    private function writeSingleBlockSection(array $config, Section $section): void
    {
        $sectionName = $section->getName();

        if (empty($config[$sectionName])) {
            return;
        }

        $this->writerAdapter->startSingleBlockSection($sectionName);
        $this->writeParams($config[$sectionName]['config']);
        $this->writerAdapter->endSingleBlockSection();
    }

    private function writeParams(array $params): void
    {
        foreach ($params as $paramName => $paramValue) {
            $this->writeParamValues($paramName, (array)$paramValue);
        }
    }

    private function writeParamValues(string $paramName, array $paramValue): void
    {
        foreach ($paramValue as $curParamValue) {
            $this->writerAdapter->writeParam($paramName, $curParamValue);
        }
    }

    private function writeMultiBlockSection(array $config, Section $section): void
    {
        foreach ($config as $blockName => $blockConfig) {
            $extends = $blockConfig['extends'] ?? null;

            $this->writerAdapter->startMultiBlockSection($section->getName(), $blockName, $extends);
            $this->writeParams($blockConfig['config']);
            $this->writerAdapter->endMultiBlockSection();
        }
    }
}