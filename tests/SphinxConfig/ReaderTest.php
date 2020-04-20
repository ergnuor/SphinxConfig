<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\Section\Reader;
use Ergnuor\SphinxConfig\Section\Reader\Adapter as ReaderAdapter;
use Ergnuor\SphinxConfig\Section\Type as SectionType;

/**
 * @uses \Ergnuor\SphinxConfig\Section\Type
 * @uses \Ergnuor\SphinxConfig\Section\Reader\Adapter
 * @uses \Ergnuor\SphinxConfig\Section
 */
class ReaderTest extends SectionCase
{
    private $adapter;
    private $reader;

    public function setUp()
    {
        $this->adapter = $this->getReaderAdapterMock();
        $this->reader = new Reader($this->adapter);
    }

    private function getReaderAdapterMock()
    {
        return $this->getMockForAbstractClass(ReaderAdapter::class);
    }

    public function testReadConfigBlocksMergedWithAndOverrideReadConfig()
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

    private function setUpReadConfigAdapterMethod($returnValue)
    {
        $this->adapter->expects($this->once())
            ->method('readConfig')
            ->with($this->configNameToRead, $this->sectionParameterObject)
            ->willReturn($returnValue);
    }

    private function setUpReadConfigBlocksAdapterMethod($returnValue)
    {
        $this->adapter->expects($this->once())
            ->method('readConfigBlocks')
            ->with($this->configNameToRead, $this->sectionParameterObject)
            ->willReturn($returnValue);
    }

    private function readConfig()
    {
        return $this->reader->readConfig(
            $this->configNameToRead,
            $this->sectionParameterObject
        );
    }

    public function testReadCurrentConfigSingleBlockTransformationsApplied()
    {
        $this->readCurrentConfigSingleBlockTransformations(['param' => 'value',]);
    }

    public function readCurrentConfigSingleBlockTransformations($readConfigData)
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

    public function testReadCurrentConfigSingleBlockTransformationsNotAppliedBecauseBlockExists()
    {
        $this->readCurrentConfigSingleBlockTransformations([SectionType::SEARCHD => ['param' => 'value',]]);
    }

    public function testReadExternalConfigSingleBlockTransformationsApplied()
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