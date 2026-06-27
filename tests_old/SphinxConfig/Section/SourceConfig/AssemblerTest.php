<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\SourceConfig;

class AssemblerTest extends AssemblerCase
{
    public function testSimplyReadConfig(): void
    {
        $this->readerAdapterStub->setSourceData(
            [
                $this->context->getConfigName() => [
                    $this->context->getSectionType() => [
                        'level1' => [
                            'extends' => 'level2',
                            'level1Param' => 'level1Value',
                        ],
                        'level2' => [
                            'level2Param' => 'level2Value',
                        ],
                    ],
                ],
            ]
        );

        $this->assertSame(
            $this->normalizeWithReadConfigContexts(
                [
                    [
                        'level1' => [
                            'extends' => 'level2',
                            'level1Param' => 'level1Value',
                        ],
                        'level2' => [
                            'level2Param' => 'level2Value',
                        ],
                    ]
                ]
            ),
            $this->assembler->assemble($this->context)
        );
    }

    private function normalizeWithReadConfigContexts(array $configs): array
    {
        $finalConfig = [];

        foreach ($configs as $readConfigName => $config) {
            if (is_numeric($readConfigName)) {
                $readConfigName = $this->context->getConfigName();
            }

            $readContext = $this->createContext(
                $readConfigName,
                $this->context->getSectionType()
            );

            $finalConfig = array_merge(
                $finalConfig,
                $this->normalizeConfig($config, $readContext)
            );
        }

        return $finalConfig;
    }

    public function testPullExternalBlocks(): void
    {
        $this->readerAdapterStub->setSourceData(
            [
                $this->context->getConfigName() => [
                    $this->context->getSectionType() => [
                        'level1' => [
                            'extends' => 'level2',
                            'level1Param' => 'level1Value',
                        ],
                        'level2' => [
                            'extends' => 'externalConfig_1@level3',
                            'level2Param' => 'level2Value',
                        ],
                    ],
                ],
                'externalConfig_1' => [
                    $this->context->getSectionType() => [
                        'level3' => [
                            'extends' => 'externalConfig_2@level4',
                            'level3Param' => 'level3Value',
                        ],
                    ],
                ],
                'externalConfig_2' => [
                    $this->context->getSectionType() => [
                        'level4' => [
                            'level4Param' => 'level4Value',
                        ],
                    ],
                ],
            ]
        );

        $this->assertSame(
            array_merge(
                $this->normalizeWithReadConfigContexts(
                    [
                        [
                            'level1' => [
                                'extends' => 'level2',
                                'level1Param' => 'level1Value',
                            ],
                            'level2' => [
                                'extends' => 'externalConfig_1@level3',
                                'level2Param' => 'level2Value',
                            ],
                        ],
                        'externalConfig_1' => [
                            'level3' => [
                                'extends' => 'externalConfig_2@level4',
                                'level3Param' => 'level3Value',
                            ],
                        ],
                        'externalConfig_2' => [
                            'level4' => [
                                'level4Param' => 'level4Value',
                            ],
                        ],
                    ]
                ),
            ),
            $this->assembler->assemble($this->context)
        );
    }

    public function testSameBlockIsNotPulledMultipleTimes(): void
    {
        $this->readerAdapterStub->setSourceData(
            [
                $this->context->getConfigName() => [
                    $this->context->getSectionType() => [
                        'level1' => [
                            'extends' => 'externalConfig_1@level2',
                            'level1Param' => 'level1Value',
                        ],
                        'level1_2' => [
                            'extends' => 'externalConfig_1@level2',
                            'level1_2Param' => 'llevel1_2Value',
                        ],
                    ],
                ],
                'externalConfig_1' => [
                    $this->context->getSectionType() => [
                        'level2' => [
                            'level3Param' => 'level3Value',
                        ],
                    ],
                ],
            ]
        );

        $this->assertSame(
            array_merge(
                $this->normalizeWithReadConfigContexts(
                    [
                        [
                            'level1' => [
                                'extends' => 'externalConfig_1@level2',
                                'level1Param' => 'level1Value',
                            ],
                            'level1_2' => [
                                'extends' => 'externalConfig_1@level2',
                                'level1_2Param' => 'llevel1_2Value',
                            ],
                        ],
                        'externalConfig_1' => [
                            'level2' => [
                                'level3Param' => 'level3Value',
                            ],
                        ],
                    ]
                ),
            ),
            $this->assembler->assemble($this->context)
        );
    }
}
