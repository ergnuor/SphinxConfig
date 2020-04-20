<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\Section;
use Ergnuor\SphinxConfig\Section\Type as SectionType;
use Ergnuor\SphinxConfig\Tests\Fake\InMemoryWriterAdapter;
use Ergnuor\SphinxConfig\Tests\Stub\ReaderAdapter;
use Ergnuor\SphinxConfig\Exception\SectionException;

class SectionExceptionTest extends SectionCase
{
    /**
     * @var InMemoryWriterAdapter
     */
    protected $writerAdapter;

    public function setUp()
    {
        $this->readerAdapter = new ReaderAdapter();
        $this->writerAdapter = new InMemoryWriterAdapter();

        $this->setCurrentConfigName('currentConfigName');
        $this->setSectionName(SectionType::SOURCE);
    }

    public function testUnknownExternalBlockReferenceException()
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

    public function testCircularReferenceDetectedException()
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

    public function testNameConflictException()
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

    public function testCircularPlaceholdersReferenceException()
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

    public function testUnknownParentBlockException()
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

    private function setExpectedSectionException($msg)
    {
        $this->expectException(SectionException::class);
        $this->expectExceptionMessage($msg);
    }
}
