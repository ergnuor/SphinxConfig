<?php

namespace Ergnuor\SphinxConfig\Section;


use Ergnuor\SphinxConfig\Exception\WriterException;
use Ergnuor\SphinxConfig\Section;

class Writer
{
    /**
     * @var Writer\Adapter|null
     */
    private $writerAdapter = null;

    /**
     * @param Writer\Adapter $writerAdapter
     */
    public function __construct(Writer\Adapter $writerAdapter)
    {
        $this->writerAdapter = $writerAdapter;
    }

    /**
     * @param array $config
     * @param Section $section
     * @throws WriterException
     */
    final public function write($config, Section $section)
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

    /**
     * @param array $config
     * @param Section $section
     */
    private function writeSingleBlockSection($config, Section $section)
    {
        $sectionName = $section->getName();

        if (empty($config[$sectionName])) {
            return;
        }

        $this->writerAdapter->startSingleBlockSection($sectionName);
        $this->writeParams($config[$sectionName]['config']);
        $this->writerAdapter->endSingleBlockSection();
    }

    private function writeParams($params)
    {
        foreach ($params as $paramName => $paramValue) {
            $this->writeParam($paramName, $paramValue);
        }
    }

    /**
     * @param string $paramName
     * @param string $paramValue
     */
    private function writeParam($paramName, $paramValue)
    {
        $paramValue = (array)$paramValue;
        foreach ($paramValue as $curParamValue) {
            $this->writerAdapter->writeParam($paramName, $curParamValue);
        }
    }

    /**
     * @param array $config
     * @param Section $section
     */
    private function writeMultiBlockSection($config, Section $section)
    {
        foreach ($config as $blockName => $blockConfig) {
            $extends = isset($blockConfig['extends']) ? $blockConfig['extends'] : null;

            $this->writerAdapter->startMultiBlockSection($section->getName(), $blockName, $extends);
            $this->writeParams($blockConfig['config']);
            $this->writerAdapter->endMultiBlockSection();
        }
    }
}