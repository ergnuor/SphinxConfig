<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\Section\Type;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    private $typeMap = [
        [
            'type' => Type::SOURCE,
            'isMultiBlock' => true,
        ],
        [
            'type' => Type::INDEX,
            'isMultiBlock' => true,
        ],
        [
            'type' => Type::INDEXER,
            'isMultiBlock' => false,
        ],
        [
            'type' => Type::SEARCHD,
            'isMultiBlock' => false,
        ],
        [
            'type' => Type::COMMON,
            'isMultiBlock' => false,
        ],
    ];


    public function testGetTypes()
    {
        $this->assertSame(
            $this->getTypes(),
            Type::getTypes()
        );
    }

    private function getTypes()
    {
        return array_column($this->typeMap, 'type');
    }

    /**
     * @dataProvider isMultiBlockTypeDataProvider
     * @param $type
     * @param $isMultiBlock
     */
    public function testIsMultiBlock($type, $isMultiBlock)
    {
            $this->assertSame(
                $isMultiBlock,
                Type::isMultiBlock($type)
            );
    }

    public function isMultiBlockTypeDataProvider()
    {
        $data = [];

        foreach($this->typeMap as $typeMap) {
            $data["'{$typeMap['type']}' is single block"] = [$typeMap['type'], $typeMap['isMultiBlock']];
        }

        return $data;
    }

    /**
     * @dataProvider isSingleBlockTypeDataProvider
     * @param $type
     * @param $isSingleBlock
     */
    public function testIsSingleBlock($type, $isSingleBlock)
    {
        $this->assertSame(
            $isSingleBlock,
            Type::isSingleBlock($type)
        );
    }

    public function isSingleBlockTypeDataProvider()
    {
        $data = [];

        foreach($this->typeMap as $typeMap) {
            $data["'{$typeMap['type']}' is single block"] = [$typeMap['type'], !$typeMap['isMultiBlock']];
        }

        return $data;
    }
}
