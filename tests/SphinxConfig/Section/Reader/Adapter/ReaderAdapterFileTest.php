<?php

namespace Ergnuor\SphinxConfig\Tests\Section\Reader\Adapter;

use Ergnuor\SphinxConfig\{Exception\ReaderException,
    Section\Reader\Adapter\File as ReaderAdapterFile,
    Tests\TestEnv\FileSystem,
    Tests\TestCase\SectionCase};
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @uses \Ergnuor\SphinxConfig\Section
 */
class ReaderAdapterFileTest extends SectionCase
{
    /**
     * @var MockObject|ReaderAdapterFile
     */
    private $adapter;

    public function setUp(): void
    {
        $this->adapter = $this->getAdapterMock(
            $this->getAdapterFileRootPath() . DIRECTORY_SEPARATOR
        );
    }

    private function getAdapterMock(string $srcPath): MockObject
    {
        return $this->getMockBuilder(ReaderAdapterFile::class)
            ->onlyMethods(['readFile'])
            ->setConstructorArgs([$srcPath])
            ->getMock();
    }

    private function getAdapterFileRootPath(): string
    {
        return FileSystem::getReaderAdapterRootPath() . 'file' . DIRECTORY_SEPARATOR;
    }

    public function testReadSectionConfigBlocksButNoSectionBlocksDirectoryExists(): void
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
     * @return InvocationMocker
     */
    private function adapterExpectations(int $numberOfCalls)
    {
        return $this->adapter
            ->expects($this->exactly($numberOfCalls))
            ->method('readFile');
    }

    private function readConfigBlocks(): array
    {
        return $this->adapter->readConfigBlocks(
            $this->configNameToRead,
            $this->sectionParameterObject
        );
    }

    public function testReadSectionConfigBlocksButNoBlocksExists(): void
    {
        $this->setUpConfigEnvironment('whatever', 'separateSectionBlocksNotExists', 'sectionName');

        $this->adapterExpectations(0);

        $blocks = $this->readConfigBlocks();

        $this->assertSame(
            [],
            $blocks
        );
    }

    public function testReadSectionConfigBlocksMixedWithDirectory(): void
    {
        $this->setUpConfigEnvironment('whatever', 'separateSectionBlocksMixedWithDirectory', 'sectionName');

        $configRootPath = $this->getAdapterFileRootPath() . $this->configNameToRead;
        $returnedBlockContent = ['blockContent'];

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

    public function testReadSectionConfigFromMultiSectionAndSingleSectionFiles(): void
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

    private function readConfig(): array
    {
        return $this->adapter->readConfig(
            $this->configNameToRead,
            $this->sectionParameterObject
        );
    }

    public function testReadSectionConfigWithMultiSectionFileCached(): void
    {
        $this->setUpConfigEnvironment('whatever', 'sectionInMultiSectionAndSingleSectionFiles', 'sectionName');

        $this->adapterExpectations(3)
            ->willReturn([]);

        $this->readConfig();
        $this->readConfig();
    }

    public function testReadSectionConfigWithMultiSectionFileCacheCleared(): void
    {
        $this->setUpConfigEnvironment('whatever', 'sectionInMultiSectionAndSingleSectionFiles', 'sectionName');

        $this->adapterExpectations(4)
            ->willReturn([]);

        $this->readConfig();

        $this->adapter->reset();

        $this->readConfig();
    }

    public function testReadSectionConfigFromMultiSectionAndSingleSectionFilesButNotExists(): void
    {
        $this->setUpConfigEnvironment('whatever', 'sectionInMultiSectionAndSingleSectionFilesNotExists', 'sectionName');

        $this->adapterExpectations(0);

        $blocks = $this->readConfig();

        $this->assertSame(
            [],
            $blocks
        );
    }

    public function testUnknownDirectoryException(): void
    {
        $this->expectException(ReaderException::class);
        $this->expectExceptionMessage("'' is not a directory");

        $this->getAdapterMock('');
    }
}