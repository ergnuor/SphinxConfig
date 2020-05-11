<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section;

/**
 * @uses \Ergnuor\SphinxConfig\Section\Utility\Type
 * @uses \Ergnuor\SphinxConfig\Section\Processor\Inheritance
 */
class WriterMultiBlockSectionTest extends WriterCase
{
    public function testReset(): void
    {
        $this->writerAdapter->expects($this->once())
            ->method('reset');

        $this->writer->reset();
    }

    public function testWriteMultiBlockSection(): void
    {
        $this->adapterExpectations(
            [
                2,
                'startMultiBlockSection',
                [
                    [$this->context->getSectionType(), 'block1', null,],
                    [$this->context->getSectionType(), 'block2', 'block1',],
                ],
            ],
            [
                2,
                'writeParam',
                [
                    ['sourceBlock1Param1', 'sourceBlock1Value1',],
                    ['sourceBlock2Param1', 'sourceBlock2Value1',],
                ],
            ],
            [2, 'endMultiBlockSection']
        );

        $this->write(
            [
                'block1' => [
                    'config' => [
                        'sourceBlock1Param1' => 'sourceBlock1Value1',
                    ],
                ],
                'block2' => [
                    'extends' => 'block1',
                    'config' => [
                        'sourceBlock2Param1' => 'sourceBlock2Value1',
                    ],
                ],

            ]
        );
    }

    public function testWriteMultiBlockSectionButSectionIsEmpty(): void
    {
        $this->adapterExpectations(
            [0, 'startMultiBlockSection'],
            [0, 'writeParam'],
            [0, 'endMultiBlockSection']
        );

        $this->write([]);
    }

    public function testWriteMultiBlockSectionButSectionBlocksConfigIsEmpty(): void
    {
        $this->adapterExpectations(
            [
                2,
                'startMultiBlockSection',
                [
                    [$this->context->getSectionType(), 'block1', null,],
                    [$this->context->getSectionType(), 'block2', 'block1',]
                ]
            ],
            [0, 'writeParam'],
            [2, 'endMultiBlockSection']
        );

        $this->write(
            [
                'block1' => [
                    'config' => [],
                ],
                'block2' => [
                    'extends' => 'block1',
                    'config' => [],
                ],

            ]
        );
    }
}