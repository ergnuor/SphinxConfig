<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\Utility;

use Ergnuor\SphinxConfig\Section\Utility\Block;
use PHPUnit\Framework\TestCase;

class BlockTest extends TestCase
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $block3;

    /**
     * @var array
     */
    private $block2;

    /**
     * @var array
     */
    private $block1;

    public function setUp(): void
    {
        $this->block3 = [
            'block3' => [
                'extends' => 'block2',
                'block3Param' => 'block3Value',
                'name' => 'block3',
                'fullBlockName' => 'config@block3',
                'config' => [],
            ],
        ];
        $this->block2 = [
            'block2' => [
                'extends' => 'block1',
                'block2Param' => 'block2Value',
                'name' => 'block2',
                'fullBlockName' => 'config@block2',
                'config' => [],
            ],
        ];
        $this->block1 = [
            'block1' => [
                'block1Param' => 'block1Value',
                'name' => 'block1',
                'fullBlockName' => 'config@block1',
                'config' => [],
            ],
        ];

        $this->updateConfig();
    }

    private function updateConfig(): void
    {
        $this->config = array_merge(
            $this->block3,
            $this->block2,
            $this->block1
        );
    }

    public function testGetParentBlock(): void
    {
        $this->assertSame(
            $this->block2['block2'],
            Block::getParentBlock($this->config, $this->block3['block3'])
        );
    }

    public function testGetParentBlocks(): void
    {
        $this->assertSame(
            array_merge(
                $this->block2,
                $this->block1,
            ),
            Block::getParentBlocks($this->config, $this->block3['block3'])
        );
    }
}
