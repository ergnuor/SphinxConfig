<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\Section\Type as SectionType;
use Ergnuor\SphinxConfig\Tests\Fake\InMemoryWriterAdapter;
use Ergnuor\SphinxConfig\Tests\Stub\ReaderAdapter;

class SectionTest extends SectionCase
{
    const CURRENT_CONFIG_NAME = 'currentConfigName';
    const SECTION_NAME = SectionType::SOURCE;

    /**
     * @var InMemoryWriterAdapter
     */
    protected $writerAdapter;

    public function setUp()
    {
        $this->readerAdapter = new ReaderAdapter();
        $this->writerAdapter = new InMemoryWriterAdapter();
    }

    /**
     * @dataProvider configTransformationsDataProvider
     *
     * @param $readerSourceData
     * @param $expectedConfig
     * @param $placeholders
     */
    public function testTransformations(
        $readerSourceData,
        $expectedConfig,
        $placeholders
    )
    {
        $this->setReaderAdapterData($readerSourceData);

        $this->initSectionAndTransformConfig($placeholders);

        $this->assertSame($expectedConfig, $this->writerAdapter->getConfig());
    }

    public function configTransformationsDataProvider()
    {
        return [
            'extends in right order' => $this->dataExtendsInRightOrder(),
            'extends external config' => $this->dataExtendsExternalConfig(),
            'external blocks didnt pulled multiple times' => $this->dataExternalBlocksDidntPulledMultipleTimes(),
            'pseudo block not appears' => $this->dataPseudoBlockNotAppears(),
            'multi value parameter inheritance' => $this->dataMultiValueParameterInheritance(),
            'placeholders' => $this->dataPlaceholders(),
            'custom params are removed' => $this->dataCustomParamsAreRemoved(),
            'multiline value is padded with slashes' => $this->dataMultilineValueIsPaddedWithSlashes(),
        ];
    }

    private function dataExtendsInRightOrder()
    {
        return [
            [
                SectionTest::CURRENT_CONFIG_NAME => [
                    SectionTest::SECTION_NAME => [
                        'level3' => ['extends' => 'level2',],
                        'level2' => ['extends' => 'level1',],
                        'level1' => [],
                    ],
                ],
            ],
            [
                SectionTest::SECTION_NAME => [
                    'level1' => [],
                    'level2' => ['extends' => 'level1',],
                    'level3' => ['extends' => 'level2',],
                ],
            ],
            []
        ];
    }

    private function dataExtendsExternalConfig()
    {
        return [
            [
                SectionTest::CURRENT_CONFIG_NAME => [
                    SectionTest::SECTION_NAME => [
                        'level3' => ['extends' => 'level2',],
                        'level2' => ['extends' => 'externalConfig@level1',],
                    ],
                ],
                'externalConfig' => [
                    SectionTest::SECTION_NAME => [
                        'level1' => [],
                    ],
                ],
            ],
            [
                SectionTest::SECTION_NAME => [
                    'level1' => [],
                    'level2' => ['extends' => 'level1',],
                    'level3' => ['extends' => 'level2',],
                ],
            ],
            []
        ];
    }

    private function dataExternalBlocksDidntPulledMultipleTimes()
    {
        return [
            [
                SectionTest::CURRENT_CONFIG_NAME => [
                    SectionTest::SECTION_NAME => [
                        'level3' => ['extends' => 'externalConfig@level1External',],
                        'level2' => ['extends' => 'externalConfig@level1External',],
                    ],
                ],
                'externalConfig' => [
                    SectionTest::SECTION_NAME => [
                        'level1External' => [],
                    ],
                ],
            ],
            [
                SectionTest::SECTION_NAME => [
                    'level1External' => [],
                    'level2' => ['extends' => 'level1External',],
                    'level3' => ['extends' => 'level1External',],
                ],
            ],
            []
        ];
    }

    private function dataPseudoBlockNotAppears()
    {
        return [
            [
                SectionTest::CURRENT_CONFIG_NAME => [
                    SectionTest::SECTION_NAME => [
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
            ],
            [
                SectionTest::SECTION_NAME => [
                    'level1' => [
                        'level1Param' => 'level1Value',
                    ],
                    'level3' => [
                        'extends' => 'level1',
                        'paramShouldBeCopied' => 'value',
                        'level3Param' => 'level3Value',
                    ],
                ],
            ],
            []
        ];
    }

    private function dataMultiValueParameterInheritance()
    {
        return [
            [
                SectionTest::CURRENT_CONFIG_NAME => [
                    SectionTest::SECTION_NAME => [
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
            ],
            [
                SectionTest::SECTION_NAME => [
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
                        'sql_query_pre' => [
                            'level3Value_1',
                            'level4AliasedValue',
                            'level3Value_2',
                        ],
                    ],
                    'level4' => [
                        'extends' => 'level3',
                        'sql_query_pre' => [
                            'level3Value_1',
                            'level4AliasedValueOverridden',
                            'level3Value_2',
                        ],
                    ],
                ],
            ],
            []
        ];
    }

    private function dataPlaceholders()
    {
        return [
            [
                SectionTest::CURRENT_CONFIG_NAME => [
                    SectionTest::SECTION_NAME => [
                        'level2' => [
                            'extends' => 'level1',

                            'global' => '::willBe.overridden:: followed by ::globalPlaceholder.another.value::',
                            'recursive' => '::recursive.placeholder::',
                            'parentBlockValuePropagated' => 'Also the ::parent.block.value:: is propagated.',
                            'implodedArrayValue' => 'Here is some imploded array placeholder value: ::arrayValuesToImplode::',
                            'valueIsNotFound' => 'And there was a silence::because.im.nonexistent.placeholder::',

                            'placeholderValues' => [
                                'arrayValuesToImplode' => [1, 2, 3, 4, 5, 6],
                                'willBe' => [
                                    'overridden' => 'Overridden global placeholder value',
                                ],
                                'recursive' => [
                                    'placeholder' => 'Recursive ::recursive.placeholderReplacement::',
                                    'placeholderReplacement' => 'placeholder replacement',
                                ]
                            ],
                        ],

                        'level1' => [
                            'trivial' => 'Normal text ::path.to.value:: placeholder value',

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
            ],
            [
                SectionTest::SECTION_NAME => [
                    'level1' => [
                        'trivial' => 'Normal text mixed with placeholder value',
                    ],
                    'level2' => [
                        'extends' => 'level1',

                        'global' => 'Overridden global placeholder value followed by another global placeholder value',
                        'recursive' => 'Recursive placeholder replacement',
                        'parentBlockValuePropagated' => 'Also the parent block value is propagated.',
                        'implodedArrayValue' => 'Here is some imploded array placeholder value: 1, 2, 3, 4, 5, 6',
                        'valueIsNotFound' => 'And there was a silence',
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
            ],
        ];
    }

    private function dataCustomParamsAreRemoved()
    {
        return [
            [
                SectionTest::CURRENT_CONFIG_NAME => [
                    SectionTest::SECTION_NAME => [
                        'level1' => [
                            'level1Param' => 'level1Value',
                            '_level1CustomParam1' => 'level1CustomValue1',
                            '_level1CustomParam2' => 'level1CustomValue2',
                        ],
                    ],
                ],
            ],
            [
                SectionTest::SECTION_NAME => [
                    'level1' => [
                        'level1Param' => 'level1Value',
                    ],
                ],
            ],
            [],
        ];
    }

    private function dataMultilineValueIsPaddedWithSlashes()
    {
        return [
            [
                SectionTest::CURRENT_CONFIG_NAME => [
                    SectionTest::SECTION_NAME => [
                        'level1' => [
                            'level1Param' => '
                            line1
                            line2
                        ',
                        ],
                    ],
                ],
            ],
            [
                SectionTest::SECTION_NAME => [
                    'level1' => [
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

    public function testGetConfigName()
    {
        $this->initSection([]);

        $this->assertSame(
            SectionTest::CURRENT_CONFIG_NAME,
            $this->section->getConfigName()
        );
    }

    public function testName()
    {
        $this->initSection([]);

        $this->assertSame(
            SectionTest::SECTION_NAME,
            $this->section->getName()
        );
    }
}
