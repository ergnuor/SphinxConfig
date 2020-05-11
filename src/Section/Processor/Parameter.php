<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section\Processor;

use Ergnuor\SphinxConfig\Exception\{Message as ExceptionMessage, SectionException};
use Ergnuor\SphinxConfig\Section\{Context, Utility\ArrayUtility, Utility\Parameter as ParameterUtility};

class Parameter
{
    /**
     * @var array
     */
    private static $processedPlaceholdersHistory = [];

    /**
     * @var array
     */
    private static $globalPlaceholderValues = [];

    /**
     * @var Context
     */
    private static $context;

    /**
     * @param Context $context
     * @param array $config
     * @param array $globalPlaceholderValues
     * @return array
     * @throws SectionException
     */
    public static function process(Context $context, array $config, array $globalPlaceholderValues): array
    {
        self::$globalPlaceholderValues = $globalPlaceholderValues;
        self::$context = $context;

        foreach ($config as $blockName => $block) {
            $config[$blockName] = self::processBlock($block);
        }

        return $config;
    }

    /**
     * @param array $block
     * @return array
     * @throws SectionException
     */
    private static function processBlock(array $block): array
    {
        foreach ($block['config'] as $paramName => $paramValue) {
            $paramValue = (array)$paramValue;

            $paramValue = self::processParamValues($paramValue, $block['placeholderValues']);

            if (!ParameterUtility::isMultiValueParam($paramName)) {
                $paramValue = array_pop($paramValue);
            }

            $block['config'][$paramName] = $paramValue;
        }
        return $block;
    }

    /**
     * @param array $paramValues
     * @param array $placeholderValues
     * @return array
     * @throws SectionException
     */
    private static function processParamValues(array $paramValues, array $placeholderValues): array
    {
        foreach ($paramValues as $key => $value) {
            $value = self::applyPlaceholdersValues($value, $placeholderValues);
            $value = self::addTrailingSlashIfNeeded($value);

            $paramValues[$key] = $value;
        }

        return $paramValues;
    }

    /**
     * @param string $value
     * @param array $placeholderValues
     * @return string
     * @throws SectionException
     */
    private static function applyPlaceholdersValues(string $value, array $placeholderValues): string
    {
        self::clearProcessedPlaceholdersHistory();

        $placeholders = self::parsePlaceholders($value);

        while (!empty($placeholders)) {
            self::checkCircularPlaceholderReference($placeholders);

            $placeholderValues = self::mixGlobalPlaceholderValues($placeholderValues);

            $value = self::replacePlaceholdersWithValues($value, $placeholders, $placeholderValues);

            self::addProcessedPlaceholdersToHistory($placeholders);
            $placeholders = self::parsePlaceholders($value);
        }

        return $value;
    }

    private static function clearProcessedPlaceholdersHistory(): void
    {
        self::$processedPlaceholdersHistory = [];
    }

    private static function parsePlaceholders(string $value): array
    {
        preg_match_all('/::(?:[^:]+?)(?=::)::/', $value, $m);

        return $m[0];
    }

    /**
     * @param $placeholders
     * @throws SectionException
     */
    private static function checkCircularPlaceholderReference($placeholders): void
    {
        if (!empty(array_intersect($placeholders, self::$processedPlaceholdersHistory))) {
            throw new SectionException(
                ExceptionMessage::forContext(
                    self::$context,
                    "Circular placeholders detected. Processed placeholders: " .
                    implode(', ', self::$processedPlaceholdersHistory) .
                    ". Next placeholders: " .
                    implode(', ', $placeholders)
                )
            );
        }
    }

    /**
     * @param array $placeholderValues
     * @return array
     */
    private static function mixGlobalPlaceholderValues(array $placeholderValues): array
    {
        $placeholderValues = array_replace_recursive(
            self::$globalPlaceholderValues,
            $placeholderValues
        );
        return $placeholderValues;
    }

    /**
     * @param array $placeholders
     * @param array $placeholderValues
     * @param string $value
     * @return string
     */
    private static function replacePlaceholdersWithValues(
        string $value,
        array $placeholders,
        array $placeholderValues
    ): string {
        foreach ($placeholders as $placeholder) {
            $placeholderValue = ArrayUtility::findByPath(
                $placeholderValues,
                self::clearPlaceholderBoundaries($placeholder)
            );

            if (is_array($placeholderValue)) {
                $placeholderValue = implode(', ', $placeholderValue);
            }

            $value = str_replace($placeholder, (string)$placeholderValue, $value);
        }
        return $value;
    }

    private static function clearPlaceholderBoundaries(string $placeholderWithBoundaries): string
    {
        $placeholderWithBoundaries = preg_replace('/^::/', '', $placeholderWithBoundaries);
        $placeholderWithBoundaries = preg_replace('/::$/', '', $placeholderWithBoundaries);

        return $placeholderWithBoundaries;
    }

    /**
     * @param array $placeholders
     */
    private static function addProcessedPlaceholdersToHistory(array $placeholders): void
    {
        self::$processedPlaceholdersHistory = array_merge(self::$processedPlaceholdersHistory, $placeholders);
    }

    private static function addTrailingSlashIfNeeded(string $value): string
    {
        return preg_replace('/([\r\n]+)/', ' \\\$1', $value);
    }
}