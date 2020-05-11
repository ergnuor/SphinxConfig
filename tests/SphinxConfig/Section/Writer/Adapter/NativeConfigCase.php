<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\Writer\Adapter;

use Ergnuor\SphinxConfig\Section\Writer\Adapter\NativeConfig;
use Ergnuor\SphinxConfig\Tests\{Section\Writer\WriterAdapterCase, TestEnv\FileSystem};

class NativeConfigCase extends WriterAdapterCase
{
    /**
     * @var string
     */
    protected $configRootPath;

    /**
     * @var string
     */
    protected $expectedFilePath;

    /**
     * @var string
     */
    protected $actualFilePath;

    public function setUp(): void
    {
        parent::setUp();
        $this->configRootPath = FileSystem::getWriterAdapterRootPath() . 'nativeConfig' . DIRECTORY_SEPARATOR;
        $this->expectedFilePath = $this->configRootPath . 'expected_' . $this->context->getConfigName() . '.conf';
        $this->actualFilePath = $this->configRootPath . $this->context->getConfigName() . '.conf';
    }

    public function tearDown(): void
    {
        if (file_exists($this->actualFilePath)) {
            unlink($this->actualFilePath);
        }
    }

    protected function setUpAdapter(string $dstPath = null): void
    {
        $this->adapter = new NativeConfig($dstPath);
    }

    protected function writeSections(): void
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

        $this->adapter->write($this->context);
    }
}