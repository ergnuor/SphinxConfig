<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Exception;

final class ConfigContextNormalizer
{
    /**
     * @param list<ConfigContext> $contexts
     * @return list<ConfigContext>
     */
    public static function normalize(array $contexts): array
    {
        /** @var list<ConfigContext> $normalizedContexts */
        $normalizedContexts = [];

        foreach ($contexts as $context) {
            if ($normalizedContexts === []) {
                $normalizedContexts[] = $context;
                continue;
            }

            $lastIndex = array_key_last($normalizedContexts);
            $lastNormalizedContext = $normalizedContexts[$lastIndex];

            if ($context->isMoreSpecificThan($lastNormalizedContext)) {
                $normalizedContexts[$lastIndex] = $context;
                continue;
            }

            if (
                $lastNormalizedContext->isMoreSpecificThan($context)
                || $lastNormalizedContext->equals($context)
            ) {
                continue;
            }

            $normalizedContexts[] = $context;
        }

        return $normalizedContexts;
    }
}
