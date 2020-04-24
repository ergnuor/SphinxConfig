<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\Section\Type;

class TypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
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


    public function testGetTypes(): void
    {
        $this->assertSame(
            $this->getTypes(),
            Type::getTypes()
        );
    }

    private function getTypes(): array
    {
        return array_column($this->typeMap, 'type');
    }

    /**
     * @dataProvider isMultiBlockTypeDataProvider
     * @param $type
     * @param $isMultiBlock
     */
    public function testIsMultiBlock(string $type, bool $isMultiBlock): void
    {
        $this->assertSame(
            $isMultiBlock,
            Type::isMultiBlock($type)
        );
    }

    public function isMultiBlockTypeDataProvider(): array
    {
        $data = [];

        foreach ($this->typeMap as $typeMap) {
            $data["'{$typeMap['type']}' is single block"] = [$typeMap['type'], $typeMap['isMultiBlock']];
        }

        return $data;
    }

    /**
     * @dataProvider isSingleBlockTypeDataProvider
     * @param $type
     * @param $isSingleBlock
     */
    public function testIsSingleBlock(string $type, bool $isSingleBlock): void
    {
        $this->assertSame(
            $isSingleBlock,
            Type::isSingleBlock($type)
        );
    }

    public function isSingleBlockTypeDataProvider(): array
    {
        $data = [];

        foreach ($this->typeMap as $typeMap) {
            $data["'{$typeMap['type']}' is single block"] = [$typeMap['type'], !$typeMap['isMultiBlock']];
        }

        return $data;
    }
}
