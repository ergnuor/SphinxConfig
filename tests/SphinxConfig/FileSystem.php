<?php

namespace Ergnuor\SphinxConfig\Tests;

class FileSystem
{
    static public function getRootPath(): string
    {
        return dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;
    }

    static public function getWriterAdapterRootPath(): string
    {
        return self::getRootPath() . 'writerAdapter' . DIRECTORY_SEPARATOR;
    }

    static public function getWriterAdapterExpectedPath(): string
    {
        return self::getWriterAdapterRootPath() . 'expected' . DIRECTORY_SEPARATOR;
    }

    static public function getWriterAdapterActualPath(): string
    {
        return self::getWriterAdapterRootPath() . 'actual' . DIRECTORY_SEPARATOR;
    }

    static public function getReaderAdapterRootPath(): string
    {
        return self::getRootPath() . 'readerAdapter' . DIRECTORY_SEPARATOR;
    }
}