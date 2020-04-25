<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\{Section\Type as SectionType,
    Tests\TestCase\SectionCase,
    Tests\TestDouble\Fake\InMemoryWriterAdapter,
    Tests\TestDouble\Stub\ReaderAdapter,
    Exception\SectionException};

/**
 * @uses \Ergnuor\SphinxConfig\Section\Reader
 * @uses \Ergnuor\SphinxConfig\Section\Writer
 * @uses \Ergnuor\SphinxConfig\Section\Type
 */
class SectionExceptionTest extends SectionCase
{
    /**
     * @var InMemoryWriterAdapter
     */
    protected $writerAdapter;

    public function setUp(): void
    {
        $this->readerAdapter = new ReaderAdapter();
        $this->writerAdapter = new InMemoryWriterAdapter();

        $this->setCurrentConfigName('currentConfigName');
        $this->setSectionName(SectionType::SOURCE);
    }

    public function testUnknownExternalBlockReferenceException(): void
    {
        $this->setExpectedSectionException(
            "An error occurred in '{$this->sectionName}' section. Unknown external block reference (externalConfig@level1). "
            . "Inheritance path: {$this->currentConfigName}@level1 -> externalConfig@level1"
        );

        $this->setReaderAdapterData([
            $this->currentConfigName => [
                $this->sectionName => [
                    'level1' => ['extends' => 'externalConfig@level1',],
                ],
            ],
        ]);

        $this->initSectionAndTransformConfig([]);
    }

    public function testCircularReferenceDetectedException(): void
    {
        $this->setExpectedSectionException(
            "An error occurred in '{$this->sectionName}' section. "
            . "Circular reference detected while pulling external blocks. "
            . "Inheritance path: {$this->currentConfigName}@level1 -> externalConfig@externalLevel1 -> externalConfig@externalLevel2 -> externalConfig@externalLevel1"
        );

        $this->setReaderAdapterData([
            $this->currentConfigName => [
                $this->sectionName => [
                    'level1' => ['extends' => 'externalConfig@externalLevel1',],
                ],
            ],
            'externalConfig' => [
                $this->sectionName => [
                    'externalLevel1' => ['extends' => 'externalConfig@externalLevel2',],
                    'externalLevel2' => ['extends' => 'externalConfig@externalLevel1',],
                ],
            ]
        ]);

        $this->initSectionAndTransformConfig([]);
    }

    public function testNameConflictException(): void
    {
        $this->setExpectedSectionException(
            "An error occurred in '{$this->sectionName}' section. There is a name conflict while pulling external blocks. "
            . "Block 'level1' already exists. "
            . "Inheritance path: {$this->currentConfigName}@level1 -> externalConfig@level1"
        );

        $this->setReaderAdapterData([
            $this->currentConfigName => [
                $this->sectionName => [
                    'level1' => ['extends' => 'externalConfig@level1',],
                ],
            ],
            'externalConfig' => [
                $this->sectionName => [
                    'level1' => [],
                ],
            ]
        ]);

        $this->initSectionAndTransformConfig([]);
    }

    public function testCircularPlaceholdersReferenceException(): void
    {
        $this->setExpectedSectionException(
            "An error occurred in '{$this->sectionName}' section. Circular placeholders detected. "
            . "Processed placeholders: ::level.first:: ,::level.second::"
        );

        $this->setReaderAdapterData([
            $this->currentConfigName => [
                $this->sectionName => [
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
        ]);

        $this->initSectionAndTransformConfig([]);
    }

    public function testUnknownParentBlockException(): void
    {
        $this->setExpectedSectionException("An error occurred in '{$this->sectionName}' section. Unknown parent block 'unknownBlock'");

        $this->setReaderAdapterData([
            $this->currentConfigName => [
                $this->sectionName => [
                    'level1' => [
                        'extends' => 'unknownBlock',
                    ],
                ],
            ],
        ]);

        $this->initSectionAndTransformConfig([]);
    }

    private function setExpectedSectionException(string $msg): void
    {
        $this->expectException(SectionException::class);
        $this->expectExceptionMessage($msg);
    }
}
