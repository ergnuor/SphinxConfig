<?php

namespace Ergnuor\SphinxConfig\Section\Reader\Adapter\File;

use Ergnuor\SphinxConfig\Section\Reader\Adapter\File;

class PhpArray extends File
{
    protected $extension = 'php';

    protected function readFile($filePath)
    {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($filePath, true);
        } else if (function_exists('apc_compile_file')) {
            apc_compile_file($filePath);
        }

        return (array)include($filePath);
    }
}