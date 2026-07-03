<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = new Finder()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

return new Config()
    ->setRiskyAllowed(false)
    ->setUsingCache(true)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache')
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules([
        '@PER-CS3x0' => true,
        '@PHP8x4Migration' => true,

        'fully_qualified_strict_types' => [
            'import_symbols' => true,
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => null,
            'import_functions' => null,
        ],
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],
        'php_unit_method_casing' => ['case' => 'camel_case'],
    ])
    ->setFinder($finder)
    ->setIndent('    ')
    ->setLineEnding("\n");
