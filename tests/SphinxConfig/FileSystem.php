<?php

namespace Ergnuor\SphinxConfig\Tests;

class FileSystem
{
    static public function getRootPath()
    {
        return dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;
    }

    static public function getWriterAdapterRootPath()
    {
        return self::getRootPath() . 'writerAdapter' . DIRECTORY_SEPARATOR;
    }

    static public function getWriterAdapterExpectedPath()
    {
        return self::getWriterAdapterRootPath() . 'expected' . DIRECTORY_SEPARATOR;
    }

    static public function getWriterAdapterActualPath()
    {
        return self::getWriterAdapterRootPath() . 'actual' . DIRECTORY_SEPARATOR;
    }

    static public function getReaderAdapterRootPath()
    {
        return self::getRootPath() . 'readerAdapter' . DIRECTORY_SEPARATOR;
    }
}