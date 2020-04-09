<?php

use Ergnuor\SphinxConfig\Exception\SectionException;
use \Ergnuor\SphinxConfig\Tests\SectionAbstract;
use \Ergnuor\SphinxConfig\Section\MultiBlock;
use \Ergnuor\SphinxConfig\Section\SourceInterface;

class MultiBlockSectionTest extends SectionAbstract
{
    public function testUnknownParentBlockException()
    {
        $this->expectException(SectionException::class);
        $this->expectExceptionMessage("An error occurred in 'source' section. Unknown parent block 'unknownBlock'");

        $sourceMock = $this->getConfigSourceMock(
            [
                'mainConfig' => [
                    'level1' => [
                        'extends' => 'unknownBlock',
                    ],
                ],
            ],
            []
        );

        $this->getSectionConfig(
            $sourceMock,
            []
        );
    }

    public function testCircularPlaceholdersReferenceException()
    {
        $this->expectException(SectionException::class);
        $this->expectExceptionMessage("An error occurred in 'source' section. Circular placeholders detected. Processed placeholders: ::level.first:: ,::level.second::");

        $sourceMock = $this->getConfigSourceMock(
            [
                'mainConfig' => [
                    'level1' => [
                        'param' => '::level.first::',

                        'placeholderValues' => [
                            'level' => [
                                'first' => '::level.second::',
                                'second' => '::level.first::',
                            ],
                        ]
                    ],
                ],
            ],
            []
        );

        $this->getSectionConfig(
            $sourceMock,
            []
        );
    }

    public function testNameConflictException()
    {
        $this->expectException(SectionException::class);
        $this->expectExceptionMessage("An error occurred in 'source' section. There is a name conflict while pulling external blocks. Block 'level1' already exists. Inheritance path: mainConfig@level1 -> externalConfig@level1");

        $sourceMock = $this->getConfigSourceMock(
            [
                'mainConfig' => [
                    'level1' => [
                        'extends' => 'externalConfig@level1',
                    ],
                ],
                'externalConfig' => [
                    'level1' => [
                    ],
                ]
            ],
            []
        );

        $this->getSectionConfig(
            $sourceMock,
            []
        );
    }

    public function testCircularReferenceDetectedException()
    {
        $this->expectException(SectionException::class);
        $this->expectExceptionMessage("An error occurred in 'source' section. Circular reference detected while pulling external blocks. Inheritance path: mainConfig@level1 -> externalConfig@externalLevel1 -> externalConfig@externalLevel2 -> externalConfig@externalLevel1");

        $sourceMock = $this->getConfigSourceMock(
            [
                'mainConfig' => [
                    'level1' => [
                        'extends' => 'externalConfig@externalLevel1',
                    ],
                ],
                'externalConfig' => [
                    'externalLevel1' => [
                        'extends' => 'externalConfig@externalLevel2',
                    ],

                    'externalLevel2' => [
                        'extends' => 'externalConfig@externalLevel1',
                    ],
                ]
            ],
            []
        );

        $this->getSectionConfig(
            $sourceMock,
            []
        );
    }

    public function testUnknownExternalBlockReferenceException()
    {
        $this->expectException(SectionException::class);
        $this->expectExceptionMessage("An error occurred in 'source' section. Unknown external block reference (externalConfig@level1). Inheritance path: mainConfig@level1 -> externalConfig@level1");

        $sourceMock = $this->getConfigSourceMock(
            [
                'mainConfig' => [
                    'level1' => [
                        'extends' => 'externalConfig@level1',
                    ],
                ],
            ],
            []
        );

        $this->getSectionConfig(
            $sourceMock,
            []
        );
    }

    /**
     * @param string $configName
     * @param string $sectionName
     * @param SourceInterface $source
     * @param array $globalPlaceholderValues
     * @return MultiBlock
     */
    protected function getSectionClass(
        SourceInterface $source,
        array $globalPlaceholderValues
    )
    {
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
            $this->dataExternalBlocksDidntPulledMultipleTimes(),
            $this->dataPseudoBlockNotAppears(),
            $this->dataMultiValueParameterInheritance(),
            $this->dataBlocksFromSeparateFilesArePrioritized(),
            $this->dataPlaceholders(),
            $this->dataCustomParamsAreRemoved(),
            $this->dataMultiLineValueIsEndedWithSlashes(),
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

    protected function dataExternalBlocksDidntPulledMultipleTimes()
    {
        return [
            [
                'mainConfig' => [
                    'level3' => [
                        'extends' => 'externalConfig@level1External',
                    ],

                    'level2' => [
                        'extends' => 'externalConfig@level1External',
                    ],
                ],
                'externalConfig' => [
                    'level1External' => [
                    ],
                ]
            ],

            [],

            [
                'level1External' => [
                    'config' => [],
                ],
                'level2' => [
                    'extends' => 'level1External',
                    'config' => [],
                ],
                'level3' => [
                    'extends' => 'level1External',
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
                        'level2Param' => '::willBe.overridden:: followed by ::globalPlaceholder.another.value::. ::recursive.placeholder::. Also the ::parent.block.value:: is propagated.',

                        'placeholderValues' => [
                            'willBe' => [
                                'overridden' => 'Overridden global placeholder value',
                            ],
                            'recursive' => [
                                'placeholder' => 'Ended with ::recursive.placeholderReplacement::',
                                'placeholderReplacement' => 'recursive placeholder replacement',
                            ]
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
                            'parent' => [
                                'block' => [
                                    'value' => 'parent block value',
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
                        'level2Param' => 'Overridden global placeholder value followed by another global placeholder value. Ended with recursive placeholder replacement. Also the parent block value is propagated.',
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

    protected function dataCustomParamsAreRemoved()
    {
        return [
            [
                'mainConfig' => [
                    'level1' => [
                        'level1Param' => 'level1Value',
                        '_level1CustomParam1' => 'level1CustomValue1',
                        '_level1CustomParam2' => 'level1CustomValue2',
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
            ],

            []
        ];
    }

    protected function dataMultiLineValueIsEndedWithSlashes()
    {
        return [
            [
                'mainConfig' => [
                    'level1' => [
                        'level1Param' => '
                            line1
                            line2
                        ',
                    ],
                ],
            ],

            [],

            [
                'level1' => [
                    'config' => [
                        'level1Param' => ' \
                            line1 \
                            line2 \
                        ',
                    ],
                ],
            ],

            []
        ];
    }
}