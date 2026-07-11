<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Exception;

final class ConfigContextFormatter
{
    /**
     * @param list<ConfigContext> $contexts
     */
    public static function format(array $contexts): string
    {
        $normalizedContexts = ConfigContextNormalizer::normalize($contexts);

        $formattedContexts = array_map(
            fn(ConfigContext $context): string => self::formatContext($context),
            $normalizedContexts,
        );

        return implode(' -> ', $formattedContexts);
    }

    private static function formatContext(ConfigContext $context): string
    {
        $parts = [
            'config: ' . $context->configName,
        ];

        if ($context->sectionName !== null) {
            $parts[] = 'section: ' . $context->sectionName;
        }

        if ($context->blockName !== null) {
            $parts[] = 'block: ' . $context->blockName;
        }

        if ($context->parameterName !== null) {
            $parts[] = 'parameter: ' . $context->parameterName;
        }

        return implode(' / ', $parts);
    }
}
