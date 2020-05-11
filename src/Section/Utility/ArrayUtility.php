<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section\Utility;

class ArrayUtility
{

    /**
     * Returns an array value found by dot separated path
     *
     * @param array $array
     * @param string $keyPath
     * @return null|string|array
     */
    public static function findByPath(array $array, string $keyPath)
    {
        $keyPathParts = explode('.', $keyPath);

        foreach ($keyPathParts as $key) {
            if (array_key_exists($key, $array)) {
                $array = $array[$key];
            } else {
                return null;
            }
        }

        return $array;
    }
}