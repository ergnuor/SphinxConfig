<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\Reader\Adapter;

use Ergnuor\SphinxConfig\Exception\ReaderException;
use Ergnuor\SphinxConfig\Section\Reader\Adapter\File as ReaderAdapterFile;
use Ergnuor\SphinxConfig\Tests\{Section\SectionCase, TestEnv\FileSystem};
use PHPUnit\Framework\MockObject\{Builder\InvocationMocker, MockObject};

/**
 * @uses \Ergnuor\SphinxConfig\Section\Processor\Inheritance
 */
class FileTest extends SectionCase
{
    /**
     * @var MockObject|ReaderAdapterFile
     */
    private $adapter;

    public function setUp(): void
    {
        parent::setUp();
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
        $this->setConfigName('separateSectionBlocksNotExists');
        $this->setSectionType('nonexistentSectionDirectory');

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
    private function adapterExpectations(int $numberOfCalls): InvocationMocker
    {
        return $this->adapter
            ->expects($this->exactly($numberOfCalls))
            ->method('readFile');
    }

    private function readConfigBlocks(): array
    {
        return $this->adapter->readSeparateBlocks($this->context);
    }

    public function testReadSectionConfigBlocksButNoBlocksExists(): void
    {
        $this->setConfigName('separateSectionBlocksNotExists');

        $this->adapterExpectations(0);

        $blocks = $this->readConfigBlocks();

        $this->assertSame(
            [],
            $blocks
        );
    }

    public function testReadSectionConfigBlocksMixedWithDirectory(): void
    {
        $this->setConfigName('separateSectionBlocksMixedWithDirectory');

        $configRootPath = $this->getAdapterFileRootPath() . $this->context->getConfigName();
        $returnedBlockContent = ['blockContent'];

        $this->adapterExpectations(2)
            ->withConsecutive(
                [
                    $this->equalTo(
                        $configRootPath . DIRECTORY_SEPARATOR . $this->context->getSectionType(
                        ) . DIRECTORY_SEPARATOR . 'block1.conf'
                    )
                ],
                [
                    $this->equalTo(
                        $configRootPath . DIRECTORY_SEPARATOR . $this->context->getSectionType(
                        ) . DIRECTORY_SEPARATOR . 'block2.conf'
                    )
                ]
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
        $this->setConfigName('sectionInMultiSectionAndSingleSectionFiles');

        $configRootPath = $this->getAdapterFileRootPath() . $this->context->getConfigName();
        $multiSectionParams = [
            'multiSectionFileParam' => 'multiSectionFileValue',
        ];
        $singleSectionParams = [
            'singleSectionFileParam' => 'singleSectionFileValue',
        ];

        $this->adapterExpectations(2)
            ->withConsecutive(
                [$this->equalTo($this->getAdapterFileRootPath() . "{$this->context->getConfigName()}.conf")],
                [$this->equalTo($configRootPath . DIRECTORY_SEPARATOR . $this->context->getSectionType() . '.conf')]
            )
            ->willReturnOnConsecutiveCalls(
                [
                    $this->context->getSectionType() => $multiSectionParams
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
        return $this->adapter->read($this->context);
    }

    public function testReadSectionConfigWithMultiSectionFileCached(): void
    {
        $this->setConfigName('sectionInMultiSectionAndSingleSectionFiles');

        $this->adapterExpectations(3)
            ->willReturn([]);

        $this->readConfig();
        $this->readConfig();
    }

    public function testReadSectionConfigWithMultiSectionFileCacheCleared(): void
    {
        $this->setConfigName('sectionInMultiSectionAndSingleSectionFiles');

        $this->adapterExpectations(4)
            ->willReturn([]);

        $this->readConfig();

        $this->adapter->reset();

        $this->readConfig();
    }

    public function testReadSectionConfigFromMultiSectionAndSingleSectionFilesButNotExists(): void
    {
        $this->setConfigName('sectionInMultiSectionAndSingleSectionFilesNotExists');

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