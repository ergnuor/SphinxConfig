<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\Config;
use Ergnuor\SphinxConfig\Section\{
    Context,
    Reader\Adapter as ReaderAdapter,
    Utility\Type as SectionType,
    Writer\Adapter as WriterAdapter
};
use Ergnuor\SphinxConfig\Tests\TestDouble\{Fake\InMemoryWriterAdapter, Stub\ReaderAdapter as ReaderAdapterStub};
use PHPUnit\Framework\{MockObject\MockObject, TestCase};

/**
 * @uses \Ergnuor\SphinxConfig\Section\Utility\Type
 * @uses \Ergnuor\SphinxConfig\Section\Processor\Inheritance
 * @uses \Ergnuor\SphinxConfig\Section\Reader
 * @uses \Ergnuor\SphinxConfig\Section\Writer
 */
class ConfigTest extends TestCase
{
    /**
     * @var ReaderAdapter|MockObject
     */
    private $readerAdapter;

    /**
     * @var WriterAdapter|MockObject
     */
    private $writerAdapter;

    public function setUp(): void
    {
        $this->readerAdapter = $this->getMockForAbstractClass(ReaderAdapter::class);
        $this->writerAdapter = $this->getMockForAbstractClass(WriterAdapter::class);
    }

    public function testPlaceholderValues(): void
    {
        $config = $this->getConfig();

        $placeholderValues = [
            'some' => [
                'placeholder' => 'value',
            ],
        ];

        $config->setPlaceholderValues($placeholderValues);

        $this->assertSame(
            $placeholderValues,
            $config->getPlaceholderValues()
        );
    }

    private function getConfig(): Config
    {
        return new Config(
            $this->readerAdapter,
            $this->writerAdapter
        );
    }

    public function testSectionsIterated(): void
    {
        $configName = 'configName';

        $config = $this->getConfig();

        $this->writerAdapter->expects($this->once())
            ->method('reset');

        $this->readerAdapter->expects($this->once())
            ->method('reset');

        $this->readerAdapter->expects($this->exactly(5))
            ->method('read')
            ->withConsecutive(
                [$this->callback($this->callbackContextEquals($configName, SectionType::SOURCE))],
                [$this->callback($this->callbackContextEquals($configName, SectionType::INDEX))],
                [$this->callback($this->callbackContextEquals($configName, SectionType::INDEXER))],
                [$this->callback($this->callbackContextEquals($configName, SectionType::SEARCHD))],
                [$this->callback($this->callbackContextEquals($configName, SectionType::COMMON))]
            )
            ->willReturn([]);

        $this->readerAdapter->expects($this->exactly(5))
            ->method('readSeparateBlocks')
            ->willReturn([]);

        $config->transform($configName);
    }

    private function callbackContextEquals(string $expectedConfigName, string $expectedSectionType): callable
    {
        return function (Context $context) use ($expectedConfigName, $expectedSectionType): bool {
            return (
                $context->getConfigName() == $expectedConfigName &&
                $context->getSectionType() == $expectedSectionType
            );
        };
    }

    public function testTransform(): void
    {
        $this->readerAdapter = new ReaderAdapterStub();
        $this->writerAdapter = new InMemoryWriterAdapter();

        $configName = 'configName';
        $this->readerAdapter->setSourceData($this->getSourceData($configName));

        $config = $this->getConfig();

        $config->setPlaceholderValues(
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
        );

        $config->transform($configName);

        $this->assertSame(
            $this->getExpectedConfig(),
            $this->writerAdapter->getConfig()
        );
    }

    private function getSourceData($configName): array
    {
        return [
            $configName => [
                SectionType::SOURCE => [
                    'src1_1' => [
                        'extends' => 'externalConfig@src2External',
                    ],
                    'src1' => [
                        'extends' => 'externalConfig@src2External',
                        'sql_query_pre' => [
                            'src1Value',
                            'src1ValueAlias' => 'src1AliasedValue',
                        ],
                        'multiLineParam' => "
                            line1
                            line2
                            line3
                        ",
                        'placeholderValues' => [
                            'path' => [
                                'to' => [
                                    'value' => 'mixed with',
                                ],
                            ],
                        ],
                    ],
                    'src2_2' => [
                        'extends' => 'src1',
                        'sql_query_pre' => [
                            'src2_2Value',
                            'src1ValueAlias' => 'src2AliasedValue',
                        ],
                    ],
                    'src2' => [
                        'extends' => 'src1',
                        'isPseudo' => true,
                        'paramWillPropagateToSrc3' => 'iWillPropagateToSrc3',
                        'sql_query_pre:clear' => [
                            'src2Value_1',
                            'src2ValueAlias' => 'src2AliasedValue',
                            'src2Value_2',
                        ],
                        'placeholderValues' => [
                            'arrayValuesToImplode' => [1, 2, 3, 4, 5, 6],
                            'globalPlaceholder' => [
                                'willBe' => [
                                    'overridden' => 'Overridden global placeholder value',
                                ],
                            ],
                        ],
                    ],
                    'src3' => [
                        'extends' => 'src2',
                        'sql_query_pre' => [
                            'src2ValueAlias' => 'src3AliasedValue',
                        ],
                        'trivial' => 'Normal text ::path.to.value:: placeholder value',
                        'global' => '::globalPlaceholder.willBe.overridden:: followed by ::globalPlaceholder.another.value::',
                        'recursive' => '::recursive.placeholder::',
                        'implodedArrayValue' => 'Some imploded array placeholder value: ::arrayValuesToImplode::',
                        'valueIsNotFound' => 'No placeholder value found::because.im.nonexistent.placeholder::',
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
                ],
                SectionType::INDEX => [
                    'src1' => [
                        'param' => 'value',
                    ]
                ],
                SectionType::SEARCHD => [
                    'param' => 'value',
                ],
                SectionType::INDEXER => [
                    'param' => 'value',
                ],
                SectionType::COMMON => [
                    'param' => 'value',
                ],
            ],
            'externalConfig' => [
                SectionType::SOURCE => [
                    'src2External' => [
                        'sql_query_pre' => 'src1ExternalValue',
                    ]
                ],
            ]
        ];
    }

    private function getExpectedConfig(): array
    {
        return [
            SectionType::SOURCE => [
                'src2External' => [
                    'sql_query_pre' => 'src1ExternalValue',
                ],
                'src1' => [
                    'extends' => 'src2External',
                    'sql_query_pre' => [
                        'src1ExternalValue',
                        'src1Value',
                        'src1AliasedValue',
                    ],
                    'multiLineParam' => ' \\
                            line1 \\
                            line2 \\
                            line3 \\
                        ',
                ],
                'src1_1' => [
                    'extends' => 'src2External',
                ],
                'src2_2' => [
                    'extends' => 'src1',
                    'sql_query_pre' => [
                        'src1ExternalValue',
                        'src1Value',
                        'src2AliasedValue',
                        'src2_2Value',
                    ],
                ],
                'src3' => [
                    'extends' => 'src1',
                    'paramWillPropagateToSrc3' => 'iWillPropagateToSrc3',
                    'sql_query_pre' => [
                        'src2Value_1',
                        'src3AliasedValue',
                        'src2Value_2',
                    ],
                    'trivial' => 'Normal text mixed with placeholder value',
                    'global' => 'Overridden global placeholder value followed by another global placeholder value',
                    'recursive' => 'Recursive placeholder replacement',
                    'implodedArrayValue' => 'Some imploded array placeholder value: 1, 2, 3, 4, 5, 6',
                    'valueIsNotFound' => 'No placeholder value found',
                ],
            ],
            SectionType::INDEX => ['src1' => ['param' => 'value',],],
            SectionType::INDEXER => ['param' => 'value',],
            SectionType::SEARCHD => ['param' => 'value',],
            SectionType::COMMON => ['param' => 'value',],
        ];
    }
}
