<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\Processor;

class ParameterTest extends ParameterCase
{
    /**
     * @dataProvider processParameterDataProvider
     *
     * @param array $expectedConfig
     * @param array $actualConfig
     * @param array|null $globalPlaceholderValues
     */
    public function testProcess(
        array $expectedConfig,
        array $actualConfig,
        array $globalPlaceholderValues = []
    ): void {
        $this->assertSame(
            $this->normalizeConfig($expectedConfig),
            $this->processParameter($actualConfig, $globalPlaceholderValues)
        );
    }

    public function processParameterDataProvider(): array
    {
        return [
            'apply placeholder values' => $this->dataApplyPlaceholderValues(),
            'multiline value is padded with slashes' => $this->dataMultilineValueIsPaddedWithSlashes(),
        ];
    }

    private function dataApplyPlaceholderValues(): array
    {
        return [
            [
                'level1' => [
                    'trivial' => 'Normal text mixed with placeholder value',
                    'global' => 'Overridden global placeholder value followed by another global placeholder value',
                    'recursive' => 'Recursive placeholder replacement',
                    'implodedArrayValue' => 'Some imploded array placeholder value: 1, 2, 3, 4, 5, 6',
                    'valueIsNotFound' => 'No placeholder value found',

                    'placeholderValues' => [
                        'path' => [
                            'to' => [
                                'value' => 'mixed with',
                            ],
                        ],
                        'arrayValuesToImplode' => [1, 2, 3, 4, 5, 6],
                        'globalPlaceholder' => [
                            'willBe' => [
                                'overridden' => 'Overridden global placeholder value',
                            ],
                        ],
                        'recursive' => [
                            'placeholder' => 'Recursive ::recursive.placeholderReplacement::',
                            'placeholderReplacement' => 'placeholder replacement',
                        ]
                    ],
                ],
            ],
            [
                'level1' => [
                    'trivial' => 'Normal text ::path.to.value:: placeholder value',
                    'global' => '::globalPlaceholder.willBe.overridden:: followed by ::globalPlaceholder.another.value::',
                    'recursive' => '::recursive.placeholder::',
                    'implodedArrayValue' => 'Some imploded array placeholder value: ::arrayValuesToImplode::',
                    'valueIsNotFound' => 'No placeholder value found::because.im.nonexistent.placeholder::',

                    'placeholderValues' => [
                        'path' => [
                            'to' => [
                                'value' => 'mixed with',
                            ],
                        ],
                        'arrayValuesToImplode' => [1, 2, 3, 4, 5, 6],
                        'globalPlaceholder' => [
                            'willBe' => [
                                'overridden' => 'Overridden global placeholder value',
                            ],
                        ],
                        'recursive' => [
                            'placeholder' => 'Recursive ::recursive.placeholderReplacement::',
                            'placeholderReplacement' => 'placeholder replacement',
                        ]
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

    private function dataMultilineValueIsPaddedWithSlashes(): array
    {
        return [
            [
                'level1' => [
                    'level1Param' => ' \
                            line1 \
                            line2 \
                        ',
                ],
            ],
            [
                'level1' => [
                    'level1Param' => '
                            line1
                            line2
                        ',
                ],
            ],
        ];
    }
}
