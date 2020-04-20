<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\Exception\ReaderException;
use Ergnuor\SphinxConfig\Section\Reader\Adapter\File as ReaderAdapterFile;

/**
 * @uses \Ergnuor\SphinxConfig\Section
 */
class ReaderAdapterFileTest extends SectionCase
{
    private $adapter;

    public function setUp()
    {
        $this->adapter = $this->getAdapterMock(
            $this->getAdapterFileRootPath() . DIRECTORY_SEPARATOR
        );
    }

    private function getAdapterMock($srcPath)
    {
        return $this->getMockBuilder(ReaderAdapterFile::class)
            ->setMethods(['readFile'])
            ->setConstructorArgs([$srcPath])
            ->getMock();
    }

    private function getAdapterFileRootPath()
    {
        return FileSystem::getReaderAdapterRootPath() . 'file' . DIRECTORY_SEPARATOR;
    }

    public function testReadSectionConfigBlocksButNoSectionBlocksDirectoryExists()
    {
        $this->setUpConfigEnvironment('whatever', 'separateSectionBlocksNotExists', 'nonexistentSectionDirectory');

        $this->adapterExpectations(0);

        $blocks = $this->readConfigBlocks();

        $this->assertSame(
            [],
            $blocks
        );
    }

    /**
     * @param $numberOfCalls
     * @return \PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    private function adapterExpectations($numberOfCalls)
    {
        return $this->adapter
            ->expects($this->exactly($numberOfCalls))
            ->method('readFile');
    }

    private function readConfigBlocks()
    {
        return $this->adapter->readConfigBlocks(
            $this->configNameToRead,
            $this->sectionParameterObject
        );
    }

    public function testReadSectionConfigBlocksButNoBlocksExists()
    {
        $this->setUpConfigEnvironment('whatever', 'separateSectionBlocksNotExists', 'sectionName');

        $this->adapterExpectations(0);

        $blocks = $this->readConfigBlocks();

        $this->assertSame(
            [],
            $blocks
        );
    }

    public function testReadSectionConfigBlocksMixedWithDirectory()
    {
        $this->setUpConfigEnvironment('whatever', 'separateSectionBlocksMixedWithDirectory', 'sectionName');

        $configRootPath = $this->getAdapterFileRootPath() . $this->configNameToRead;
        $returnedBlockContent = 'blockContent';

        $this->adapterExpectations(2)
            ->withConsecutive(
                [$this->equalTo($configRootPath . DIRECTORY_SEPARATOR . $this->sectionName . DIRECTORY_SEPARATOR . 'block1.conf')],
                [$this->equalTo($configRootPath . DIRECTORY_SEPARATOR . $this->sectionName . DIRECTORY_SEPARATOR . 'block2.conf')]
            )
            ->willReturn($returnedBlockContent);

        $blocks = $this->readConfigBlocks();

        $this->assertSame(
            [
                'block1' => $returnedBlockContent,
                'block2' => $returnedBlockContent,
            ],
            $blocks
        );
    }

    public function testReadSectionConfigFromMultiSectionAndSingleSectionFiles()
    {
        $this->setUpConfigEnvironment('whatever', 'sectionInMultiSectionAndSingleSectionFiles', 'sectionName');

        $configRootPath = $this->getAdapterFileRootPath() . $this->configNameToRead;
        $multiSectionParams = [
            'multiSectionFileParam' => 'multiSectionFileValue',
        ];
        $singleSectionParams = [
            'singleSectionFileParam' => 'singleSectionFileValue',
        ];

        $this->adapterExpectations(2)
            ->withConsecutive(
                [$this->equalTo($this->getAdapterFileRootPath() . "{$this->configNameToRead}.conf")],
                [$this->equalTo($configRootPath . DIRECTORY_SEPARATOR . $this->sectionName . '.conf')]
            )
            ->willReturnOnConsecutiveCalls(
                [
                    $this->sectionName => $multiSectionParams
                ],
                $singleSectionParams
            );

        $blocks = $this->readConfig();

        $this->assertSame(
            array_replace(
                $multiSectionParams,
                $singleSectionParams
            ),
            $blocks
        );
    }

    private function readConfig()
    {
        return $this->adapter->readConfig(
            $this->configNameToRead,
            $this->sectionParameterObject
        );
    }

    public function testReadSectionConfigWithMultiSectionFileCached()
    {
        $this->setUpConfigEnvironment('whatever', 'sectionInMultiSectionAndSingleSectionFiles', 'sectionName');

        $this->adapterExpectations(3)
            ->willReturn([]);

        $this->readConfig();
        $this->readConfig();
    }

    public function testReadSectionConfigWithMultiSectionFileCacheCleared()
    {
        $this->setUpConfigEnvironment('whatever', 'sectionInMultiSectionAndSingleSectionFiles', 'sectionName');

        $this->adapterExpectations(4)
            ->willReturn([]);

        $this->readConfig();

        $this->adapter->reset();

        $this->readConfig();
    }

    public function testReadSectionConfigFromMultiSectionAndSingleSectionFilesButNotExists()
    {
        $this->setUpConfigEnvironment('whatever', 'sectionInMultiSectionAndSingleSectionFilesNotExists', 'sectionName');

        $this->adapterExpectations(0);

        $blocks = $this->readConfig();

        $this->assertSame(
            [],
            $blocks
        );
    }

    public function testUnknownDirectoryException()
    {
        $this->expectException(ReaderException::class);
        $this->expectExceptionMessage("'' is not a directory");

        $this->getAdapterMock('');
    }
}