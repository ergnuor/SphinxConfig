<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section;

use Ergnuor\SphinxConfig\Section\Utility\Type as SectionType;

/**
 * @uses \Ergnuor\SphinxConfig\Section\Utility\Type
 * @uses \Ergnuor\SphinxConfig\Section\Processor\Inheritance
 */
class WriterSingleBlockSectionTest extends WriterCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setSectionType(SectionType::SEARCHD);
    }

    public function testWriteSingleBlockSection(): void
    {
        $this->adapterExpectations(
            [1, 'startSingleBlockSection', [[$this->context->getSectionType()]]],
            [1, 'writeParam', [['searchdParam1', 'searchdValue1']]],
            [1, 'endSingleBlockSection']
        );

        $this->write(
            [
                $this->context->getSectionType() => [
                    'config' => [
                        'searchdParam1' => 'searchdValue1',
                    ],
                ],
            ]
        );
    }

    public function testWriteSingleBlockSectionButSectionIsEmpty(): void
    {
        $this->adapterExpectations(
            [0, 'startSingleBlockSection'],
            [0, 'writeParam'],
            [0, 'endSingleBlockSection']
        );

        $this->write([$this->context->getSectionType() => [],]);
    }

    public function testWriteSingleBlockSectionButSectionConfigIsEmpty(): void
    {
        $this->adapterExpectations(
            [0, 'startSingleBlockSection', [[$this->context->getSectionType()]]],
            [0, 'writeParam'],
            [0, 'endSingleBlockSection']
        );

        $this->write(
            [
                $this->context->getSectionType() => [
                    'config' => [],
                ],
            ]
        );
    }
}