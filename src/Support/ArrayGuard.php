<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Support;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;

final class ArrayGuard
{
    private const int DISPLAYED_UNKNOWN_KEYS_LIMIT = 10;

    /**
     * @param array<array-key, mixed> $array
     * @param list<array-key> $allowedKeys
     */
    public static function requireOnlyAllowedKeys(
        array $array,
        array $allowedKeys,
        string $unsupportedKeysMessage,
    ): void {
        $unknownKeys = array_keys(
            array_diff_key(
                $array,
                array_fill_keys($allowedKeys, true)
            )
        );

        if ($unknownKeys === []) {
            return;
        }

        $shownKeys = array_slice($unknownKeys, 0, self::DISPLAYED_UNKNOWN_KEYS_LIMIT);
        $shownKeys = array_map(
            fn(int|string $key) => var_export($key, true),
            $shownKeys,
        );
        $hiddenKeysCount = count($unknownKeys) - count($shownKeys);

        $message = $unsupportedKeysMessage . ' Unknown keys: ' . implode(', ', $shownKeys);

        if ($hiddenKeysCount > 0) {
            $message .= " and $hiddenKeysCount more";
        }

        $message .= '.';

        throw new InvalidArgumentException($message);
    }
}
