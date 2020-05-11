<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section\SourceConfig;

use Ergnuor\SphinxConfig\Section\{Context, Utility\Parameter};

class Normalizer
{
    /**
     * @var Context
     */
    private $sourceConfigContext;

    public function __construct(Context $sourceConfigContext)
    {
        $this->sourceConfigContext = $sourceConfigContext;
    }

    public function normalize(Context $readContext, array $config): array
    {
        foreach ($config as $blockName => $block) {
            $block = (array)$block;
            $block = $this->removeCustomParams($block);

            $block = $this->normalizeExtension($block);

            $block['config'] = $block['config'] ?? [];
            $block['config'] = array_merge(
                $this->extractSphinxParams($block),
                $block['config']
            );
            $block = $this->extractSystemParams($block);

            $block['name'] = $blockName;
            $block['fullBlockName'] = $readContext->getConfigName() . '@' . $blockName;
            $block['isPseudo'] = (bool)($block['isPseudo'] ?? false);
            $block['placeholderValues'] = (array)($block['placeholderValues'] ?? []);

            $config[$blockName] = $this->normalizeSphinxConfig($block);
        }

        return $config;
    }

    private function removeCustomParams(array $block): array
    {
        return array_filter(
            $block,
            function ($paramName) {
                return !Parameter::isCustomParam($paramName);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    private function normalizeExtension(array $block): array
    {
        if (!isset($block['extends'])) {
            return $block;
        }

        $extendParts = explode('@', $block['extends']);
        if (isset($extendParts[1])) {
            $block['extendsConfig'] = $extendParts[0];
            $block['extends'] = $extendParts[1];
        } else {
            $block['extendsConfig'] = $this->sourceConfigContext->getConfigName();
        }

        return $block;
    }

    private function extractSphinxParams(array $block): array
    {
        return array_diff_key(
            $block,
            array_flip(Parameter::getSystemParamsList())
        );
    }

    private function extractSystemParams(array $block): array
    {
        return array_intersect_key(
            $block,
            array_flip(Parameter::getSystemParamsList())
        );
    }

    private function normalizeSphinxConfig(array $block): array
    {
        $block['paramModifier'] = [];
        $normalizedConfig = [];

        foreach ($block['config'] as $paramName => $paramValue) {
            ['name' => $paramName, 'modifier' => $paramModifier] = Parameter::parseName($paramName);

            if (!is_null($paramModifier)) {
                $block['paramModifier'][$paramName] = $paramModifier;
            }

            if (Parameter::isMultiValueParam($paramName)) {
                $paramValue = (array)$paramValue;
            }

            $normalizedConfig[$paramName] = $paramValue;
        }

        $block['config'] = $normalizedConfig;

        return $block;
    }
}