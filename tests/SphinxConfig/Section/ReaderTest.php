<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section;

use PHPUnit\Framework\MockObject\MockObject;
use Ergnuor\SphinxConfig\Section\{Reader, Reader\Adapter as ReaderAdapter, Utility\Type as SectionType};

/**
 * @uses \Ergnuor\SphinxConfig\Section\Utility\Type
 * @uses \Ergnuor\SphinxConfig\Section\Processor\Inheritance
 */
class ReaderTest extends SectionCase
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var MockObject|ReaderAdapter
     */
    private $readerAdapter;

    public function setUp(): void
    {
        parent::setUp();
        $this->readerAdapter = $this->getReaderAdapterMock();
        $this->reader = new Reader($this->readerAdapter);
    }

    private function getReaderAdapterMock(): MockObject
    {
        return $this->getMockForAbstractClass(ReaderAdapter::class);
    }

    public function testReset(): void
    {
        $this->readerAdapter->expects($this->once())
            ->method('reset');

        $this->reader->reset();
    }

    public function testReadConfigBlocksMergedWithAndOverrideReadConfig(): void
    {
        $this->setUpReadAdapterMethod(
            [
                'block1' => [],
                'block2' => ['param' => 'willBeOverwritten',],
            ]
        );
        $this->setUpReadSeparateBlocksAdapterMethod(
            [
                'block2' => ['param' => 'overwrites',],
                'block3' => [],
            ]
        );

        $config = $this->readConfig();

        $this->assertSame(
            [
                'block1' => [],
                'block2' => ['param' => 'overwrites',],
                'block3' => [],
            ],
            $config
        );
    }

    private function setUpReadAdapterMethod(array $returnValue): void
    {
        $this->readerAdapter->expects($this->once())
            ->method('read')
            ->with($this->context)
            ->willReturn($returnValue);
    }

    private function setUpReadSeparateBlocksAdapterMethod(array $returnValue): void
    {
        $this->readerAdapter->expects($this->once())
            ->method('readSeparateBlocks')
            ->with($this->context)
            ->willReturn($returnValue);
    }

    private function readConfig(): array
    {
        return $this->reader->read($this->context);
    }

    public function testReadCurrentConfigSingleBlockSectionTransformationsApplied(): void
    {
        $this->readCurrentConfigSingleBlockSectionTransformations(['param' => 'value',]);
    }

    public function readCurrentConfigSingleBlockSectionTransformations(array $readConfigData): void
    {
        $this->setSectionType(SectionType::SEARCHD);

        $this->setUpReadAdapterMethod($readConfigData);
        $this->setUpReadSeparateBlocksAdapterMethod(['block1' => [],]);

        $config = $this->readConfig();

        $this->assertSame(
            [
                SectionType::SEARCHD => [
                    'param' => 'value',
                ],
                'block1' => [
                    'isPseudo' => true,
                ],
            ],
            $config
        );
    }

    public function testReadCurrentConfigSingleBlockSectionTransformationsNotAppliedBecauseBlockExists(): void
    {
        $this->readCurrentConfigSingleBlockSectionTransformations([SectionType::SEARCHD => ['param' => 'value',]]);
    }
}