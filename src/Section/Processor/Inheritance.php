<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section\Processor;

use Ergnuor\SphinxConfig\Exception\{Message as ExceptionMessage, SectionException};
use Ergnuor\SphinxConfig\Section\{Context, Utility\Block, Utility\Parameter};

class Inheritance
{
    /**
     * @var array
     */
    private static $config = [];

    /**
     * @var Context
     */
    private static $context;

    /**
     * @param Context $context
     * @param array $config
     * @return array
     * @throws SectionException
     */
    public static function process(Context $context, array $config): array
    {
        self::$config = $config;
        self::$context = $context;

        self::checkInheritance();
        self::sortByInheritanceLevel();

        foreach (self::$config as $blockName => $block) {
            $block = self::mergeMultiValueParamsIfNeeded($block);
            $block = self::propagatePseudoBlockParams($block);
            $block = self::propagatePlaceholderValues($block);

            self::$config[$blockName] = $block;
        }

        return self::$config;
    }


    /**
     * @throws SectionException
     */
    private static function checkInheritance(): void
    {
        foreach (self::$config as $blockName => $block) {
            self::checkBlockInheritance($block);
        }
    }

    /**
     * @param $block
     * @throws SectionException
     */
    private static function checkBlockInheritance(array $block): void
    {
        $inheritancePath = [];
        $nextBlock = $block;
        while (isset($nextBlock['extends'])) {
            if (in_array($nextBlock['fullBlockName'], $inheritancePath)) {
                $inheritancePath[] = $nextBlock['fullBlockName'];

                throw new SectionException(
                    ExceptionMessage::forContext(
                        self::$context,
                        'Circular inheritance detected. Inheritance path: ' .
                        implode(' -> ', $inheritancePath)
                    )
                );
            }

            $inheritancePath[] = $nextBlock['fullBlockName'];

            $extends = $nextBlock['extends'];
            if (!isset(self::$config[$extends])) {
                throw new SectionException(
                    ExceptionMessage::forContext(
                        self::$context,
                        "Parent block '{$nextBlock['extendsConfig']}@{$extends}' does not exists"
                    )
                );
            }

            $nextBlock = self::$config[$extends];
        }
    }

    /**
     * @throws SectionException
     */
    private static function sortByInheritanceLevel(): void
    {
        foreach (self::$config as $blockName => $block) {
            if (isset(self::$config[$blockName]['inheritanceLevel'])) {
                continue;
            }

            self::setInheritanceLevel($block);
        }

        uksort(
            self::$config,
            function ($aBlockName, $bBlockName) {
                $a = self::$config[$aBlockName]['inheritanceLevel'];
                $b = self::$config[$bBlockName]['inheritanceLevel'];

                if ($a == $b) {
                    return strcmp($aBlockName, $bBlockName);
                }
                return $a <=> $b;
            }
        );
    }

    /**
     * @param array $block
     * @throws SectionException
     */
    private static function setInheritanceLevel(array $block): void
    {
        $parentBlocks = Block::getParentBlocks(self::$config, $block);

        $inheritanceLevel = 1;
        $nextBlock = array_pop($parentBlocks);
        while (!is_null($nextBlock)) {
            self::$config[$nextBlock['name']]['inheritanceLevel'] = $inheritanceLevel++;
            $nextBlock = array_pop($parentBlocks);
        }

        self::$config[$block['name']]['inheritanceLevel'] = $inheritanceLevel;
    }

    /**
     * @param array $block
     * @return array
     * @throws SectionException
     */
    private static function mergeMultiValueParamsIfNeeded(array $block): array
    {
        foreach ($block['config'] as $paramName => $paramValue) {
            $paramModifier = $block['paramModifier'][$paramName] ?? null;

            if (
                !Parameter::isMultiValueParam($paramName) ||
                $paramModifier == 'clear'
            ) {
                continue;
            }

            $block['config'][$paramName] = self::mergeMultiValueParamWithParent($paramName, $paramValue, $block);
        }

        return $block;
    }

    /**
     * @param string $paramName
     * @param array $paramValues
     * @param array $block
     * @return array
     * @throws SectionException
     */
    private static function mergeMultiValueParamWithParent(
        string $paramName,
        array $paramValues,
        array $block
    ): array {
        $parentBlock = self::getClosestParentBlockContainsParam($paramName, $block);

        if (is_null($parentBlock)) {
            return $paramValues;
        }

        return array_merge(
            (array)$parentBlock['config'][$paramName],
            $paramValues
        );
    }

    /**
     * @param string $paramName
     * @param array $block
     * @return array|null
     * @throws SectionException
     */
    public static function getClosestParentBlockContainsParam(string $paramName, array $block): ?array
    {
        return Block::getParentBlock(
            self::$config,
            $block,
            function ($parentBlock) use ($paramName) {
                return isset($parentBlock['config'][$paramName]);
            }
        );
    }

    /**
     * @param array $block
     * @return array
     * @throws SectionException
     */
    private static function propagatePseudoBlockParams(array $block): array
    {
        $parentBlock = Block::getParentBlock(self::$config, $block);

        if (
            !is_null($parentBlock) &&
            $parentBlock['isPseudo']
        ) {
            $block['config'] = array_replace(
                $parentBlock['config'],
                $block['config']
            );
        }
        return $block;
    }

    /**
     * @param array $block
     * @return array
     * @throws SectionException
     */
    private static function propagatePlaceholderValues(array $block): array
    {
        $parentBlock = Block::getParentBlock(self::$config, $block);

        if (!is_null($parentBlock)) {
            $block['placeholderValues'] = array_replace_recursive(
                $parentBlock['placeholderValues'],
                $block['placeholderValues']
            );
        }

        return $block;
    }
}