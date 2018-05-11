<?php

use \Ergnuor\SphinxConfig\Tests\TestCase;
use \Ergnuor\SphinxConfig\Section\Writer\Adapter\SphinxConfigFile;
use \Ergnuor\SphinxConfig\Exception\WriterException;


class SectionWriterAdapterSphinxConfigFileTest extends TestCase
{
    public function testPathRequiredException()
    {
        $this->expectException(WriterException::class);
        $this->expectExceptionMessage('Destination path required');

        $this->writeBlockByPath('blockName', null, 'parentBlockName', 'sectionName');
    }

    public function testDestinationFileIsNotWtirable()
    {
        $dirPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'unknownDirectory';

        $this->expectException(WriterException::class);
        $this->expectExceptionMessage("Destination directory '{$dirPath}' is not writable");

        $this->writeBlockByPath('blockName', $dirPath, 'parentBlockName', 'sectionName');
    }

    public function testWritesMultiBlockSection()
    {
        $this->writeBlock('blockName', 'parentBlockName', 'sectionName');

        $testPath = $this->getTestPath();

        $this->assertStringEqualsFile(
            $testPath . DIRECTORY_SEPARATOR . 'SphinxConfigFile.conf',
            "sectionName blockName : parentBlockName {" . PHP_EOL . "\tparamName = paramValue" . PHP_EOL . "}" . PHP_EOL . PHP_EOL
        );
    }

    public function testWritesSingleBlockSection()
    {
        $this->writeBlock('sectionName');

        $testPath = $this->getTestPath();

        $this->assertStringEqualsFile(
            $testPath . DIRECTORY_SEPARATOR . 'SphinxConfigFile.conf',
            "sectionName {" . PHP_EOL . "\tparamName = paramValue" . PHP_EOL . "}" . PHP_EOL . PHP_EOL
        );
    }

    protected function writeBlock($blockName, $extends = null, $sectionName = null)
    {
        $this->writeBlockByPath($blockName, $this->getTestPath(), $extends, $sectionName);
    }

    protected function writeBlockByPath($blockName, $path, $extends = null, $sectionName = null)
    {
        $writer = new SphinxConfigFile($path);
        $writer->beforeWriteConfig('SphinxConfigFile');
        $writer->startBlock($blockName, $extends, $sectionName);
        $writer->writeParam('paramName', 'paramValue');
        $writer->endBlock();
        $writer->afterWriteConfig('SphinxConfigFile');
    }

    protected function getTestPath()
    {
        return $this->getConfigRoot() . DIRECTORY_SEPARATOR . 'Writer' . DIRECTORY_SEPARATOR . 'SphinxConfigFile';
    }
}