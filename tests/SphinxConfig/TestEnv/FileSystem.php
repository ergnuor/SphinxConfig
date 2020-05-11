<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\TestEnv;

class FileSystem
{
    static public function getRootPath(): string
    {
        return TESTS_ROOT . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;
    }

    static public function getWriterAdapterRootPath(): string
    {
        return self::getRootPath() . 'writerAdapter' . DIRECTORY_SEPARATOR;
    }

    static public function getReaderAdapterRootPath(): string
    {
        return self::getRootPath() . 'readerAdapter' . DIRECTORY_SEPARATOR;
    }
}