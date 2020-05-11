<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\Writer\Adapter;

use ReflectionClass;

class NativeConfigTest extends NativeConfigCase
{
    public function testWrite(): void
    {
        $this->setUpAdapter($this->configRootPath);

        $this->writeSections();

        $this->assertFileEquals(
            $this->expectedFilePath,
            $this->actualFilePath
        );
    }

    public function testStartMultiBlockSection(): void
    {
        $this->startMultiBlockSectionTest();
    }

    private function startMultiBlockSectionTest(string $extends = null): void
    {
        $this->setUpAdapter();
        $blockName = 'blockName';

        $this->adapter->startMultiBlockSection($this->context->getSectionType(), $blockName, $extends);

        $this->assertEquals(
            $this->getFullMultiBlockSectionName($blockName, $extends),
            $this->getAdapterBufferValue()
        );
    }

    private function getFullMultiBlockSectionName(string $blockName, string $extends = null): string
    {
        $fullBlockName = "{$this->context->getSectionType()} {$blockName}";
        if (isset($extends)) {
            $fullBlockName .= ' : ' . $extends;
        }

        return "{$fullBlockName} {" . PHP_EOL;
    }

    private function getAdapterBufferValue(): string
    {
        $reflection = new ReflectionClass(get_class($this->adapter));
        $property = $reflection->getProperty('buffer');
        $property->setAccessible(true);
        return $property->getValue($this->adapter);
    }

    public function testStartMultiBlockSectionWithExtension(): void
    {
        $this->startMultiBlockSectionTest('extendedSectionName');
    }

    public function testEndMultiBlockSection(): void
    {
        $this->setUpAdapter();

        $this->adapter->endMultiBlockSection();

        $this->assertEquals(
            $this->getSectionEnd(),
            $this->getAdapterBufferValue()
        );
    }

    private function getSectionEnd(): string
    {
        return "}" . PHP_EOL . PHP_EOL;
    }

    public function testStartSingleBlockSection(): void
    {
        $this->setUpAdapter();

        $this->adapter->startSingleBlockSection($this->context->getSectionType());

        $this->assertEquals(
            "{$this->context->getSectionType()} {" . PHP_EOL,
            $this->getAdapterBufferValue()
        );
    }

    public function testEndSingleBlockSection(): void
    {
        $this->setUpAdapter();

        $this->adapter->endSingleBlockSection();

        $this->assertEquals(
            $this->getSectionEnd(),
            $this->getAdapterBufferValue()
        );
    }

    public function testWriteParam(): void
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

    public function testReset(): void
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