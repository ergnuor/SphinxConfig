<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\Section\Type as SectionType;

/**
 * @uses \Ergnuor\SphinxConfig\Section\Type
 * @uses \Ergnuor\SphinxConfig\Section\Writer\Adapter
 * @uses \Ergnuor\SphinxConfig\Section
 */
class WriterMultiBlockSectionTest extends WriterCase
{
    public function setUp()
    {
        $this->setSectionName(SectionType::SOURCE);
        parent::setUp();
    }

    public function testWriteMultiBlockSection()
    {
        $this->adapterExpectations(
            [2, 'startMultiBlockSection',
                [
                    [$this->sectionName, 'block1', null,],
                    [$this->sectionName, 'block2', 'block1',]
                ]
            ],
            [2, 'writeParam',
                [
                    ['sourceBlock1Param1', 'sourceBlock1Value1',],
                    ['sourceBlock2Param1', 'sourceBlock2Value1',]
                ]
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

    public function testWriteMultiBlockSectionButSectionIsEmpty()
    {
        $this->adapterExpectations(
            [0, 'startMultiBlockSection'],
            [0, 'writeParam'],
            [0, 'endMultiBlockSection']
        );

        $this->write([]);
    }

    public function testWriteMultiBlockSectionButSectionBlocksConfigIsEmpty()
    {
        $this->adapterExpectations(
            [2, 'startMultiBlockSection',
                [
                    [$this->sectionName, 'block1', null,],
                    [$this->sectionName, 'block2', 'block1',]
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