<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\Section;
use Ergnuor\SphinxConfig\Section\Reader\Adapter as ReaderAdapter;
use Ergnuor\SphinxConfig\Section\Writer\Adapter as WriterAdapter;

class SectionCase extends TestCase
{
    protected $currentConfigName;
    protected $configNameToRead;
    protected $sectionName;
    protected $sectionParameterObject;
    /**
     * @var Section
     */
    protected $section;

    /**
     * @var \Ergnuor\SphinxConfig\Section\Reader\Adapter
     */
    protected $readerAdapter;

    /**
     * @var \Ergnuor\SphinxConfig\Section\Writer\Adapter
     */
    protected $writerAdapter;

    protected function setUpConfigEnvironment(
        $currentConfigName,
        $configNameToRead,
        $sectionName
    )
    {
        $this->setCurrentConfigName($currentConfigName);
        $this->setConfigNameToRead($configNameToRead);
        $this->setSectionName($sectionName);
        $this->setUpSectionParameterObject();
    }

    protected function setCurrentConfigName($name)
    {
        $this->currentConfigName = $name;
    }

    protected function setConfigNameToRead($name)
    {
        $this->configNameToRead = $name;
    }

    protected function setSectionName($name)
    {
        $this->sectionName = $name;
    }

    protected function setUpSectionParameterObject()
    {
        $this->sectionParameterObject = $this->getSectionParameterObject(
            $this->currentConfigName,
            $this->sectionName
        );
    }

    protected function getSectionParameterObject($configName, $sectionName)
    {
        return new Section(
            $configName,
            $sectionName,
            $this->createMock(ReaderAdapter::class),
            $this->createMock(WriterAdapter::class),
            []
        );
    }

    protected function setConfigName($name)
    {
        $this->setCurrentConfigName($name);
        $this->setConfigNameToRead($name);
    }

    protected function initSectionAndTransformConfig($placeholders)
    {
        $this->initSection($placeholders);
        $this->section->transform();
    }

    protected function initSection($placeholders)
    {
        $this->section = new Section(
            SectionTest::CURRENT_CONFIG_NAME,
            SectionTest::SECTION_NAME,
            $this->readerAdapter,
            $this->writerAdapter,
            $placeholders
        );
    }


    protected function setReaderAdapterData($data)
    {
        $this->readerAdapter->setSourceData($data);
    }
}