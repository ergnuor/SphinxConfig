<?php

namespace Ergnuor\SphinxConfig\Section\Source;

class PhpArray extends FileAbstract
{
    protected $extension = 'php';

    protected function readFile($filePath)
    {
        return (array)include($filePath);
    }
}