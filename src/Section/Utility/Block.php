<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section\Utility;

use Ergnuor\SphinxConfig\Exception\SectionException;

class Block
{
    /**
     * @param array $config
     * @param array $block
     * @param callable|null $filter
     * @return array|null
     * @throws SectionException
     */
    public static function getParentBlock(array $config, array $block, callable $filter = null): ?array
    {
        $iterator = Block::getIterator();
        foreach ($iterator($config, $block, $filter) as $parentBlock) {
            return $parentBlock;
        }

        return null;
    }

    private static function getIterator(): callable
    {
        return function (array $config, array $block, callable $filter = null) {
            while (isset($block['extends'])) {
                if (!isset($config[$block['extends']])) {
                    throw new SectionException(
                        "Unknown parent block '{$block['extends']}' for block '{$block['fullBlockName']}'"
                    );
                }

                $parentBlock = $config[$block['extends']];

                if (
                    is_null($filter) ||
                    call_user_func_array($filter, [$parentBlock])
                ) {
                    yield $parentBlock;
                }
                $block = $parentBlock;
            }
        };
    }

    /**
     * @param array $config
     * @param array $block
     * @param callable|null $filter
     * @return array
     * @throws SectionException
     */
    public static function getParentBlocks(array $config, array $block, callable $filter = null): array
    {
        $parentBlocks = [];
        $iterator = Block::getIterator();
        foreach ($iterator($config, $block, $filter) as $parentBlock) {
            $parentBlocks[$parentBlock['name']] = $parentBlock;
        }

        return $parentBlocks;
    }
}