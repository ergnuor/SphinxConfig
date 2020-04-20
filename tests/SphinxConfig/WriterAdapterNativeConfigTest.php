<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\Exception\WriterException;
use Ergnuor\SphinxConfig\Section\Writer\Adapter\NativeConfig;

class WriterAdapterNativeConfigTest extends SectionCase
{
    private $expectedFilePath;
    private $actualFilePath;

    /**
     * @var NativeConfig
     */
    private $adapter;

    public function setUp()
    {
        $this->setCurrentConfigName('writerAdapterNativeConfig');
        $this->expectedFilePath = FileSystem::getWriterAdapterExpectedPath() . $this->currentConfigName . '.conf';
        $this->actualFilePath = FileSystem::getWriterAdapterActualPath() . $this->currentConfigName . '.conf';
    }

    public function tearDown()
    {
        if (file_exists($this->actualFilePath)) {
            unlink($this->actualFilePath);
        }
    }

    public function testDestinationDirectoryIsNotDirectoryException()
    {
        $dstPath = FileSystem::getWriterAdapterRootPath() . 'unknownDirectory';
        $this->expectException(WriterException::class);
        $this->expectExceptionMessage("'{$dstPath}' is not a directory");

        $this->setUpAdapter($dstPath);

        $this->writeSections();
    }

    private function setUpAdapter($dstPath = null)
    {
        $this->adapter = new NativeConfig($dstPath);
    }

    protected function writeSections()
    {
        $this->adapter->startSingleBlockSection('singleBlockSection');
        $this->adapter->writeParam('singleBlockSection_param1', 'singleBlockSection_value1');
        $this->adapter->writeParam('singleBlockSection_param2', 'singleBlockSection_value2');
        $this->adapter->endSingleBlockSection();

        $this->adapter->startMultiBlockSection('multiBlockSection1', 'block1');
        $this->adapter->writeParam('multiBlockSection1_param1', 'multiBlockSection1_value1');
        $this->adapter->writeParam('multiBlockSection1_param2', 'multiBlockSection1_value2');
        $this->adapter->endSingleBlockSection();

        $this->adapter->startMultiBlockSection('multiBlockSection2', 'block1', 'multiBlockSection1');
        $this->adapter->writeParam('multiBlockSection2_param3', 'multiBlockSection2_value3');
        $this->adapter->writeParam('multiBlockSection2_param4', 'multiBlockSection2_value4');
        $this->adapter->endSingleBlockSection();

        $this->adapter->write($this->currentConfigName);
    }

    public function testWrite()
    {
        $this->setUpAdapter(FileSystem::getWriterAdapterActualPath());

        $this->writeSections();

        $this->assertFileEquals(
            $this->expectedFilePath,
            $this->actualFilePath
        );
    }

    public function testStartMultiBlockSection()
    {
        $this->startMultiBlockSectionTest();
    }

    private function startMultiBlockSectionTest($extends = null)
    {
        $this->setUpAdapter();
        $this->setSectionName('sectionName');
        $blockName = 'blockName';

        $this->adapter->startMultiBlockSection($this->sectionName, $blockName, $extends);

        $this->assertEquals(
            $this->getFullMultiBlockSectionName($blockName, $extends),
            $this->getAdapterBufferValue()
        );
    }

    private function getFullMultiBlockSectionName($blockName, $extends = null)
    {
        $fullBlockName = "{$this->sectionName} {$blockName}";
        if (isset($extends)) {
            $fullBlockName .= ' : ' . $extends;
        }

        return "{$fullBlockName} {" . PHP_EOL;
    }

    private function getAdapterBufferValue()
    {
        return $this->getValueOfInaccessibleProperty($this->adapter, 'buffer');
    }

    public function testStartMultiBlockSectionWithExtension()
    {
        $this->startMultiBlockSectionTest('extendedSectionName');
    }

    public function testEndMultiBlockSection()
    {
        $this->setUpAdapter();

        $this->adapter->endMultiBlockSection();

        $this->assertEquals(
            $this->getSectionEnd(),
            $this->getAdapterBufferValue()
        );
    }

    private function getSectionEnd()
    {
        return "}" . PHP_EOL . PHP_EOL;
    }

    public function testStartSingleBlockSection()
    {
        $this->setUpAdapter();
        $this->setSectionName('sectionName');

        $this->adapter->startSingleBlockSection($this->sectionName);

        $this->assertEquals(
            "{$this->sectionName} {" . PHP_EOL,
            $this->getAdapterBufferValue()
        );
    }

    public function testEndSingleBlockSection()
    {
        $this->setUpAdapter();

        $this->adapter->endSingleBlockSection();

        $this->assertEquals(
            $this->getSectionEnd(),
            $this->getAdapterBufferValue()
        );
    }

    public function testWriteParam()
    {
        $this->setUpAdapter();
        $paramName = 'paramName';
        $paramValue = 'paramValue';

        $this->adapter->writeParam($paramName, $paramValue);

        $this->assertEquals(
            "\t{$paramName} = $paramValue" . PHP_EOL,
            $this->getAdapterBufferValue()
        );
    }

    public function testReset()
    {
        $this->setUpAdapter();
        $paramName = 'paramName';
        $paramValue = 'paramValue';

        $this->adapter->writeParam($paramName, $paramValue);
        $this->adapter->reset();

        $this->assertEquals(
            '',
            $this->getAdapterBufferValue()
        );
    }
}