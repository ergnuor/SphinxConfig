<?php

namespace Ergnuor\SphinxConfig\Tests\Section;

use Ergnuor\SphinxConfig\Tests\TestCase\SectionCase;
use PHPUnit\Framework\MockObject\MockObject;
use Ergnuor\SphinxConfig\Section\{
    Reader,
    Reader\Adapter as ReaderAdapter,
    Type as SectionType
};

/**
 * @uses \Ergnuor\SphinxConfig\Section\Type
 * @uses \Ergnuor\SphinxConfig\Section
 */
class ReaderTest extends SectionCase
{
    /**
     * @var MockObject|ReaderAdapter
     */
    private $adapter;

    /**
     * @var Reader
     */
    private $reader;

    public function setUp(): void
    {
        $this->adapter = $this->getReaderAdapterMock();
        $this->reader = new Reader($this->adapter);
    }

    private function getReaderAdapterMock(): MockObject
    {
        return $this->getMockForAbstractClass(ReaderAdapter::class);
    }

    public function testReadConfigBlocksMergedWithAndOverrideReadConfig(): void
    {
        $this->setUpConfigEnvironment('whatever', 'configName', SectionType::SOURCE);

        $this->setUpReadConfigAdapterMethod([
            'block1' => [],
            'block2' => ['param' => 'willBeOverwritten',],
        ]);
        $this->setUpReadConfigBlocksAdapterMethod([
            'block2' => ['param' => 'overwrites',],
            'block3' => [],
        ]);

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

    private function setUpReadConfigAdapterMethod(array $returnValue): void
    {
        $this->adapter->expects($this->once())
            ->method('readConfig')
            ->with($this->configNameToRead, $this->sectionParameterObject)
            ->willReturn($returnValue);
    }

    private function setUpReadConfigBlocksAdapterMethod(array $returnValue)
    {
        $this->adapter->expects($this->once())
            ->method('readConfigBlocks')
            ->with($this->configNameToRead, $this->sectionParameterObject)
            ->willReturn($returnValue);
    }

    private function readConfig(): array
    {
        return $this->reader->readConfig(
            $this->configNameToRead,
            $this->sectionParameterObject
        );
    }

    public function testReadCurrentConfigSingleBlockTransformationsApplied(): void
    {
        $this->readCurrentConfigSingleBlockTransformations(['param' => 'value',]);
    }

    public function readCurrentConfigSingleBlockTransformations(array $readConfigData): void
    {
        $this->setUpConfigEnvironment('currentConfig', 'currentConfig', SectionType::SEARCHD);

        $this->setUpReadConfigAdapterMethod($readConfigData);
        $this->setUpReadConfigBlocksAdapterMethod(['block1' => [],]);

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

    public function testReadCurrentConfigSingleBlockTransformationsNotAppliedBecauseBlockExists(): void
    {
        $this->readCurrentConfigSingleBlockTransformations([SectionType::SEARCHD => ['param' => 'value',]]);
    }

    public function testReadExternalConfigSingleBlockTransformationsApplied(): void
    {
        $this->setUpConfigEnvironment('currentConfig', 'externalConfig', SectionType::SEARCHD);

        $this->setUpReadConfigAdapterMethod(['param' => 'value',]);
        $this->setUpReadConfigBlocksAdapterMethod(['block1' => [],]);

        $config = $this->readConfig();

        $this->assertSame(
            [
                SectionType::SEARCHD => [
                    'param' => 'value',
                    'isPseudo' => true,
                ],
                'block1' => [
                    'isPseudo' => true,
                ],
            ],
            $config
        );
    }
}