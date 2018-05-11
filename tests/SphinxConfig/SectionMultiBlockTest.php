<?php

use \Ergnuor\SphinxConfig\Tests\SectionAbstract;
use \Ergnuor\SphinxConfig\Section\MultiBlock;
use \Ergnuor\SphinxConfig\Section\SourceInterface;

class SectionMultiBlockTest extends SectionAbstract
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
        return new MultiBlock(
            'mainConfig',
            'source',
            $source,
            $globalPlaceholderValues
        );
    }

    public function configProvider()
    {
        return [
            $this->dataExtendsInRightOrder(),
            $this->dataExtendsExternalConfig(),
            $this->dataPseudoBlockNotAppears(),
            $this->dataMultiValueParameterInheritance(),
            $this->dataBlocksFromSeparateFilesArePrioritized(),
            $this->dataPlaceholders(),
        ];
    }

    protected function dataExtendsInRightOrder()
    {
        return [
            [
                'mainConfig' => [
                    'level3' => [
                        'extends' => 'level2',
                    ],

                    'level2' => [
                        'extends' => 'level1',
                    ],

                    'level1' => [
                    ],
                ]
            ],

            [],

            [
                'level1' => [
                    'config' => [],
                ],
                'level2' => [
                    'extends' => 'level1',
                    'config' => [],
                ],
                'level3' => [
                    'extends' => 'level2',
                    'config' => [],
                ],
            ],

            []
        ];
    }

    protected function dataExtendsExternalConfig()
    {
        return [
            [
                'mainConfig' => [
                    'level3' => [
                        'extends' => 'level2',
                    ],

                    'level2' => [
                        'extends' => 'externalConfig@level1',
                    ],
                ],
                'externalConfig' => [
                    'level1' => [
                    ],
                ]
            ],

            [],

            [
                'level1' => [
                    'config' => [],
                ],
                'level2' => [
                    'extends' => 'level1',
                    'config' => [],
                ],
                'level3' => [
                    'extends' => 'level2',
                    'config' => [],
                ],
            ],

            []
        ];
    }

    protected function dataPseudoBlockNotAppears()
    {
        return [
            [
                'mainConfig' => [
                    'level3' => [
                        'extends' => 'level2',
                        'level3Param' => 'level3Value',
                    ],

                    'level2' => [
                        'extends' => 'level1',
                        'isPseudo' => true,
                        'paramShouldBeCopied' => 'value',
                    ],

                    'level1' => [
                        'level1Param' => 'level1Value',
                    ],
                ],
            ],

            [],

            [
                'level1' => [
                    'config' => [
                        'level1Param' => 'level1Value',
                    ],
                ],
                'level3' => [
                    'extends' => 'level1',
                    'config' => [
                        'paramShouldBeCopied' => 'value',
                        'level3Param' => 'level3Value',
                    ],
                ],
            ],

            []
        ];
    }

    protected function dataMultiValueParameterInheritance()
    {
        return [
            [
                'mainConfig' => [
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
            ],

            [],

            [
                'level1' => [
                    'config' => [
                        'sql_query_pre' => [
                            'level1Value',
                        ],
                    ],
                ],

                'level2' => [
                    'extends' => 'level1',
                    'config' => [
                        'sql_query_pre' => [
                            'level1Value',
                            'level2Value',
                        ],
                    ],
                ],

                'level3' => [
                    'extends' => 'level2',
                    'config' => [
                        'sql_query_pre' => [
                            'level3Value_1',
                            'valueAlias' => 'level4AliasedValue',
                            'level3Value_2',
                        ],
                    ],
                ],

                'level4' => [
                    'extends' => 'level3',
                    'config' => [
                        'sql_query_pre' => [
                            'level3Value_1',
                            'valueAlias' => 'level4AliasedValueOverridden',
                            'level3Value_2',
                        ],
                    ],
                ],
            ],

            []
        ];
    }

    protected function dataBlocksFromSeparateFilesArePrioritized()
    {
        return [
            [
                'mainConfig' => [
                    'level2' => [
                        'extends' => 'level1',
                        'level2Param' => 'level2Value',
                    ],

                    'level1' => [
                        'level1Param' => 'level1Value',
                    ],
                ],
            ],

            [
                'mainConfig' => [
                    'level2' => [
                        'extends' => 'level1',
                        'level2ParamFromSeparateFile' => 'level2ValueFromSeparateFile',
                    ],
                ],
            ],

            [
                'level1' => [
                    'config' => [
                        'level1Param' => 'level1Value',
                    ],
                ],
                'level2' => [
                    'extends' => 'level1',
                    'config' => [
                        'level2ParamFromSeparateFile' => 'level2ValueFromSeparateFile',
                    ],
                ],
            ],

            []
        ];
    }

    protected function dataPlaceholders()
    {
        return [
            [
                'mainConfig' => [
                    'level2' => [
                        'extends' => 'level1',
                        'level2Param' => '::willBe.overridden:: followed by ::globalPlaceholder.another.value::',

                        'placeholderValues' => [
                            'willBe' => [
                                'overridden' => 'Overridden global placeholder value',
                            ],
                        ],
                    ],

                    'level1' => [
                        'level1Param' => 'Normal text ::path.to.value:: placeholder value',

                        'placeholderValues' => [
                            'path' => [
                                'to' => [
                                    'value' => 'mixed with',
                                ],
                            ],
                        ],
                    ],

                ],
            ],

            [],

            [
                'level1' => [
                    'config' => [
                        'level1Param' => 'Normal text mixed with placeholder value',
                    ],
                ],
                'level2' => [
                    'extends' => 'level1',
                    'config' => [
                        'level2Param' => 'Overridden global placeholder value followed by another global placeholder value',
                    ],
                ],
            ],

            [
                'globalPlaceholder' => [
                    'willBe' => [
                        'overridden' => 'I will be overridden',
                    ],
                    'another' => [
                        'value' => 'another global placeholder value',
                    ],
                ],
            ]
        ];
    }
}