<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\Processor;

class InheritanceTest extends InheritanceCase
{
    /**
     * @dataProvider processInheritanceDataProvider
     *
     * @param array $expectedConfig
     * @param array $actualConfig
     * @throws \Ergnuor\SphinxConfig\Exception\SectionException
     */
    public function testProcess(array $expectedConfig, array $actualConfig): void
    {
        $this->assertSame(
            $this->normalizeExpectedConfig($expectedConfig),
            $this->processInheritance($actualConfig)
        );
    }

    private function normalizeExpectedConfig(array $config): array
    {
        $config = $this->normalizeConfig($config);

        $inheritanceLevel = 1;

        foreach ($config as $blockName => $block) {
            $config[$blockName]['inheritanceLevel'] = $inheritanceLevel++;
        }

        return $config;
    }

    public function processInheritanceDataProvider(): array
    {
        return [
            'sorted by inheritance level' => $this->dataSortedByInheritanceLevel(),
            'multi value params' => $this->dataMultiValueParams(),
            'pseudo block params are propagated' => $this->dataPseudoBlockParamsArePropagated(),
            'placeholder values are propagated' => $this->dataPlaceholderValuesArePropagated(),
        ];
    }

    private function dataSortedByInheritanceLevel(): array
    {
        return [
            [
                'level3' => [],
                'level2' => [
                    'extends' => 'level3',
                ],
                'level1' => [
                    'extends' => 'level2',
                ],
            ],
            [
                'level3' => [
                ],
                'level1' => [
                    'extends' => 'level2',
                ],
                'level2' => [
                    'extends' => 'level3',
                ],
            ]
        ];
    }

    private function dataMultiValueParams(): array
    {
        return [
            [
                'level1' => [
                    'sql_query_pre' => 'level1Value',
                ],
                'level2' => [
                    'extends' => 'level1',
                    'sql_query_pre' => [
                        'level1Value',
                        'level2Value',
                    ],
                ],
                'level3' => [
                    'extends' => 'level2',
                    'sql_query_pre:clear' => [
                        'level3Value_1',
                        'valueAlias' => 'level4AliasedValue',
                        'level3Value_2',
                    ],
                ],
                'level4' => [
                    'extends' => 'level3',
                    'sql_query_pre' => [
                        'level3Value_1',
                        'valueAlias' => 'level4AliasedValueOverridden',
                        'level3Value_2',
                    ],
                ],
            ],
            [
                'level4' => [
                    'extends' => 'level3',
                    'sql_query_pre' => [
                        'valueAlias' => 'level4AliasedValueOverridden',
                    ],
                ],
                'level3' => [
                    'extends' => 'level2',
                    'sql_query_pre:clear' => [
                        'level3Value_1',
                        'valueAlias' => 'level4AliasedValue',
                        'level3Value_2',
                    ],
                ],
                'level2' => [
                    'extends' => 'level1',
                    'sql_query_pre' => [
                        'level2Value',
                    ],
                ],
                'level1' => [
                    'sql_query_pre' => 'level1Value',
                ],
            ],
        ];
    }

    private function dataPseudoBlockParamsArePropagated(): array
    {
        return [
            [
                'level1' => [
                    'level1Param' => 'level1Value',
                ],
                'level2' => [
                    'extends' => 'level1',
                    'isPseudo' => true,
                    'paramWillPropagate' => 'value',
                    'commonParameter' => 'willBeOverwritten',
                ],
                'level3' => [
                    'extends' => 'level2',
                    'paramWillPropagate' => 'value',
                    'commonParameter' => 'willOverwrite',
                    'level3Param' => 'level3Value',
                ],
            ],
            [
                'level3' => [
                    'extends' => 'level2',
                    'level3Param' => 'level3Value',
                    'commonParameter' => 'willOverwrite',
                ],

                'level2' => [
                    'extends' => 'level1',
                    'isPseudo' => true,
                    'paramWillPropagate' => 'value',
                    'commonParameter' => 'willBeOverwritten',
                ],

                'level1' => [
                    'level1Param' => 'level1Value',
                ],
            ],
        ];
    }

    private function dataPlaceholderValuesArePropagated(): array
    {
        return [
            [
                'level1' => [
                    'placeholderValues' => [
                        'level1Param' => 'level1Value',
                    ],
                ],
                'level2' => [
                    'extends' => 'level1',
                    'isPseudo' => true,
                    'placeholderValues' => [
                        'level1Param' => 'level1Value',
                        'level2Param' => [
                            'level2UniqueParam' => 'level2UniqueValue',
                            'level2CommonParam' => 'willBeOverwritten',
                        ],
                    ],
                ],
                'level3' => [
                    'extends' => 'level2',
                    'placeholderValues' => [
                        'level1Param' => 'level1Value',
                        'level2Param' => [
                            'level2UniqueParam' => 'level2UniqueValue',
                            'level2CommonParam' => 'willOverwrite',
                        ],
                        'level3Param' => 'level3Value',
                    ],
                ],
            ],
            [
                'level3' => [
                    'extends' => 'level2',
                    'placeholderValues' => [
                        'level3Param' => 'level3Value',
                        'level2Param' => [
                            'level2CommonParam' => 'willOverwrite',
                        ],
                    ],
                ],

                'level2' => [
                    'extends' => 'level1',
                    'isPseudo' => true,
                    'placeholderValues' => [
                        'level2Param' => [
                            'level2UniqueParam' => 'level2UniqueValue',
                            'level2CommonParam' => 'willBeOverwritten',
                        ],
                    ],
                ],

                'level1' => [
                    'placeholderValues' => [
                        'level1Param' => 'level1Value',
                    ],
                ],
            ],
        ];
    }
}
