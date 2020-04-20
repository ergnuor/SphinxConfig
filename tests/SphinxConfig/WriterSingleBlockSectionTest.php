<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\Section\Type as SectionType;


/**
 * @uses \Ergnuor\SphinxConfig\Section\Type
 * @uses \Ergnuor\SphinxConfig\Section\Writer\Adapter
 * @uses \Ergnuor\SphinxConfig\Section
 */
class WriterSingleBlockSectionTest extends WriterCase
{
    public function setUp()
    {
        $this->setSectionName(SectionType::SEARCHD);
        parent::setUp();
    }

    public function testWriteSingleBlockSection()
    {
        $this->adapterExpectations(
            [1, 'startSingleBlockSection', [[$this->sectionName]]],
            [1, 'writeParam', [['searchdParam1', 'searchdValue1']]],
            [1, 'endSingleBlockSection']
        );

        $this->write([
            $this->sectionName => [
                'config' => [
                    'searchdParam1' => 'searchdValue1',
                ],
            ],
        ]);
    }

    public function testWriteSingleBlockSectionButSectionIsEmpty()
    {
        $this->adapterExpectations(
            [0, 'startSingleBlockSection'],
            [0, 'writeParam'],
            [0, 'endSingleBlockSection']
        );

        $this->write([$this->sectionName => [],]);
    }

    public function testWriteSingleBlockSectionButSectionConfigIsEmpty()
    {
        $this->adapterExpectations(
            [1, 'startSingleBlockSection', [[$this->sectionName]]],
            [0, 'writeParam'],
            [1, 'endSingleBlockSection']
        );

        $this->write(
            [
                $this->sectionName => [
                    'config' => [],
                ],
            ]
        );
    }
}