<?php

use \Ergnuor\SphinxConfig\Tests\SectionAbstract;
use \Ergnuor\SphinxConfig\Section\SingleBlock;
use \Ergnuor\SphinxConfig\Section\SourceInterface;

class SingleBlockSectionTest extends SectionAbstract
{
    /**
     * @param string $configName
     * @param string $sectionName
     * @param SourceInterface $source
     * @param array $globalPlaceholderValues
     * @return \Ergnuor\SphinxConfig\Section\MultiBlock
     */
    protected function getSectionClass(
        SourceInterface $source,
        array $globalPlaceholderValues
    ) {
        return new SingleBlock(
            'mainConfig',
            'indexer',
            $source,
            $globalPlaceholderValues
        );
    }

    public function configProvider()
    {
        return [
            [
                [
                    'mainConfig' => [
                        'indexerParam' => 'indexerValue',
                        'extends' => 'separateFileBlock',
                    ],
                ],

                [
                    'mainConfig' => [
                        'separateFileBlock' => [
                            'indexerParam_separateFileBlock' => 'indexerValue_separateFileBlock',
                            'extends' => 'externalConfig@externalSeparateFileBlock',
                        ],
                    ],

                    'externalConfig' => [
                        'externalSeparateFileBlock' => [
                            'indexerParam_externalConfig_separateFileBlock' => 'indexerValue_externalConfig_separateFileBlock',
                        ],
                    ],
                ],

                [
                    'indexerParam_externalConfig_separateFileBlock' => 'indexerValue_externalConfig_separateFileBlock',
                    'indexerParam_separateFileBlock' => 'indexerValue_separateFileBlock',
                    'indexerParam' => 'indexerValue',
                ],

                []
            ],
        ];
    }
}