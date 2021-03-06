<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section\Reader\Adapter\File;

use Ergnuor\SphinxConfig\Section\Reader\Adapter\File;

class PhpArray extends File
{
    protected $extension = 'php';

    protected function readFile(string $filePath): array
    {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($filePath, true);
        } elseif (function_exists('apc_compile_file')) {
            apc_compile_file($filePath);
        }

        return include($filePath);
    }
}