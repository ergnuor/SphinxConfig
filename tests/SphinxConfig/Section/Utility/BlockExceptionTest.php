<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\Utility;

use Ergnuor\SphinxConfig\Exception\SectionException;
use Ergnuor\SphinxConfig\Section\Utility\Block;
use PHPUnit\Framework\TestCase;

class BlockExceptionTest extends TestCase
{
    public function testGetParentBlockNotFoundException(): void
    {
        $block = [
            'extends' => 'nonexistentParentBlock',
            'fullBlockName' => 'config@block1'
        ];

        $this->expectException(SectionException::class);
        $this->expectExceptionMessage(
            "Unknown parent block '{$block['extends']}' for block '{$block['fullBlockName']}'"
        );

        $this->assertSame(
            [],
            Block::getParentBlocks([], $block)
        );
    }
}
