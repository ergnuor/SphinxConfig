<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section\Processor;

use Ergnuor\SphinxConfig\Exception\SectionException;
use Ergnuor\SphinxConfig\Section\Utility\Block;

class Cleaner
{
    /**
     * @param array $config
     * @return array
     * @throws SectionException
     */
    public static function process(array $config): array
    {
        $config = self::filterPseudoBlocks($config);
        return self::filterPrivateParameters($config);
    }

    /**
     * @param array $config
     * @return array
     * @throws SectionException
     */
    private static function filterPseudoBlocks(array $config): array
    {
        $realBlocks = self::getRealBlocks($config);

        foreach ($realBlocks as $blockName => $block) {
            $realBlocks[$blockName] = self::fixInheritanceParams($config, $block);
        }

        return $realBlocks;
    }

    private static function getRealBlocks(array $config): array
    {
        return array_filter(
            $config,
            function ($block) {
                return !$block['isPseudo'];
            }
        );
    }

    /**
     * @param array $config
     * @param array $block
     * @return array
     * @throws SectionException
     */
    private static function fixInheritanceParams(array $config, array $block): array
    {
        $closestParentRealBlock = self::getClosestParentRealBlock($config, $block);

        if (!is_null($closestParentRealBlock)) {
            $block['extends'] = $closestParentRealBlock['name'];
        } else {
            unset(
                $block['extends'],
                $block['extendsConfig']
            );
        }

        return $block;
    }

    /**
     * Returns the closest parent block for the specified block that is not a pseudo block
     * @param array $config
     * @param array $block
     * @return array|null
     * @throws SectionException
     */
    private static function getClosestParentRealBlock(array $config, array $block): ?array
    {
        return Block::getParentBlock(
            $config,
            $block,
            function ($parentBlock) {
                return !$parentBlock['isPseudo'];
            }
        );
    }

    private static function filterPrivateParameters(array $config): array
    {
        $cleanConfig = [];
        foreach ($config as $blockName => $block) {
            $cleanConfig[$blockName] = self::filterBlockPrivateParameters($block);
        }

        return $cleanConfig;
    }

    private static function filterBlockPrivateParameters(array $block): array
    {
        return array_intersect_key(
            $block,
            array_flip(
                [
                    'extends',
                    'config',
                ]
            )
        );
    }
}