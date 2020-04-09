<?php

namespace Ergnuor\SphinxConfig;


use Ergnuor\SphinxConfig\Section;
use Ergnuor\SphinxConfig\Section\SourceInterface;

abstract class SphinxConfigAbstract
{
    const SECTION_SOURCE = 'source';
    const SECTION_INDEX = 'index';
    const SECTION_INDEXER = 'indexer';
    const SECTION_SEARCHD = 'searchd';
    const SECTION_COMMON = 'common';

    /**
     * Global placeholder values
     *
     * @var array
     */
    private $placeholderValues = [];

    /**
     * @var null|Section\SourceInterface
     */
    private $sourceObject = null;

    /**
     * @var null|Section\Writer\AdapterInterface
     */
    private $writerObject = null;


    /**
     * @param Section\SourceInterface $sourceObject
     * @param Section\Writer\AdapterInterface $writerObject
     */
    public function __construct(
        Section\SourceInterface $sourceObject = null,
        Section\Writer\AdapterInterface $writerObject = null
    )
    {
        $this->sourceObject = $sourceObject;
        $this->writerObject = $writerObject;
    }

    /**
     * @param string $configName
     * @return $this
     */
    public function make($configName)
    {
        $sectionList = [
            self::SECTION_SOURCE => Section\MultiBlock::class,
            self::SECTION_INDEX => Section\MultiBlock::class,
            self::SECTION_INDEXER => Section\SingleBlock::class,
            self::SECTION_SEARCHD => Section\SingleBlock::class,
            self::SECTION_COMMON => Section\SingleBlock::class,
        ];

        $writerAdapter = $this->getWriterAdapterObject();
        $writerAdapter->beforeWriteConfig($configName);

        $sourceObject = $this->getSectionSourceObject();
        $sourceObject->beforeReadSections();

        foreach ($sectionList as $sectionName => $sectionClassName) {
            $section = $this->getSectionObject($sectionClassName, $configName, $sectionName, $sourceObject);
            $writer = $this->getWriterObject($section, $writerAdapter);
            $writer->writeSection($section);
        }

        $writerAdapter->afterWriteConfig($configName);
        $sourceObject->afterReadSections();

        return $this;
    }

    /**
     * Sets global placeholder values
     *
     * @param array $placeholderValues
     * @return SphinxConfigAbstract
     */
    public function setPlaceholderValues(array $placeholderValues)
    {
        $this->placeholderValues = $placeholderValues;
        return $this;
    }

    /**
     * Gets global placeholder values
     *
     * @return array
     */
    public function getPlaceholderValues()
    {
        return $this->placeholderValues;
    }

    /**
     * Returns the writer object, depending on the type of section
     *
     * @param Section\MultiBlock $section
     * @param Section\Writer\AdapterInterface $writerAdapter
     * @return Section\Writer\MultiBlock|Section\WriterAbstract
     */
    private function getWriterObject(Section\MultiBlock $section, Section\Writer\AdapterInterface $writerAdapter)
    {
        $sectionClassName = get_class($section);

        switch ($sectionClassName) {
            case Section\MultiBlock::class:
                return new Section\Writer\MultiBlock($writerAdapter);
                break;
            case Section\SingleBlock::class:
                return new Section\Writer\SingleBlock($writerAdapter);
                break;
            default:
                throw new \InvalidArgumentException("Unknown section class {$sectionClassName}");
                break;
        }
    }

    /**
     * Creates and initializes a section object
     *
     * @param string $sectionClassName
     * @param string $configName
     * @param string $sectionName
     * @param SourceInterface $sourceObject
     * @return Section\MultiBlock
     */
    private function getSectionObject(
        $sectionClassName,
        $configName,
        $sectionName,
        SourceInterface $sourceObject
    )
    {
        return new $sectionClassName(
            $configName,
            $sectionName,
            $sourceObject,
            $this->getPlaceholderValues()
        );
    }

    /**
     * Specifies the object to load the source config
     *
     * @return SourceInterface
     */
    protected function getSectionSourceObject()
    {
        return $this->sourceObject;
    }

    /**
     * Specifies the object to generate the resulting configs
     *
     * @return Section\Writer\AdapterInterface
     */
    protected function getWriterAdapterObject()
    {
        return $this->writerObject;
    }
}