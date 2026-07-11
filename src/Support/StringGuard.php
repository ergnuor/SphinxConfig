<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Support;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;

final class StringGuard
{
    public static function requireTrimmedNotEmpty(string $value, string $message): string
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException($message);
        }

        return $value;
    }
}
