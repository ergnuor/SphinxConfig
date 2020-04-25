<?php

namespace Ergnuor\SphinxConfig\Tests\TestCase;

use PHPUnit\Framework\MockObject\MockObject;
use Ergnuor\SphinxConfig\{Section,
    Section\Reader\Adapter as ReaderAdapter,
    Section\Writer\Adapter as WriterAdapter,
    Tests\SectionTest,
    Tests\TestCase\TestCase};


class SectionCase extends TestCase
{
    /**
     * @var string
     */
    protected $currentConfigName;

    /**
     * @var string
     */
    protected $configNameToRead;

    /**
     * @var string
     */
    protected $sectionName;

    /**
     * @var Section
     */
    protected $sectionParameterObject;

    /**
     * @var Section
     */
    protected $section;

    /**
     * @var MockObject|ReaderAdapter
     */
    protected $readerAdapter;

    /**
     * @var MockObject|WriterAdapter
     */
    protected $writerAdapter;

    protected function setUpConfigEnvironment(
        $currentConfigName,
        $configNameToRead,
        $sectionName
    ): void
    {
        $this->setCurrentConfigName($currentConfigName);
        $this->setConfigNameToRead($configNameToRead);
        $this->setSectionName($sectionName);
        $this->setUpSectionParameterObject();
    }

    protected function setCurrentConfigName(string $name): void
    {
        $this->currentConfigName = $name;
    }

    protected function setConfigNameToRead(string $name): void
    {
        $this->configNameToRead = $name;
    }

    protected function setSectionName(string $name): void
    {
        $this->sectionName = $name;
    }

    protected function setUpSectionParameterObject(): void
    {
        $this->sectionParameterObject = $this->getSectionParameterObject(
            $this->currentConfigName,
            $this->sectionName
        );
    }

    protected function getSectionParameterObject(string $configName, string $sectionName): Section
    {
        return new Section(
            $configName,
            $sectionName,
            $this->createMock(ReaderAdapter::class),
            $this->createMock(WriterAdapter::class),
            []
        );
    }

    protected function setConfigName(string $name): void
    {
        $this->setCurrentConfigName($name);
        $this->setConfigNameToRead($name);
    }

    protected function initSectionAndTransformConfig(array $placeholders): void
    {
        $this->initSection($placeholders);
        $this->section->transform();
    }

    protected function initSection(array $placeholders): void
    {
        $this->section = new Section(
            SectionTest::CURRENT_CONFIG_NAME,
            SectionTest::SECTION_NAME,
            $this->readerAdapter,
            $this->writerAdapter,
            $placeholders
        );
    }


    protected function setReaderAdapterData(array $data): void
    {
        $this->readerAdapter->setSourceData($data);
    }
}