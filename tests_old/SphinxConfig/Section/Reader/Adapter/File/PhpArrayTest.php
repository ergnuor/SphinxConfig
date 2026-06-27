<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\Reader\Adapter\File;

use Ergnuor\SphinxConfig\Section\Reader\Adapter\File\PhpArray as PhpArrayAdapter;
use Ergnuor\SphinxConfig\Tests\{Section\SectionCase, TestEnv\FileSystem};

/**
 * @uses \Ergnuor\SphinxConfig\Section\Reader\Adapter\File
 * @uses \Ergnuor\SphinxConfig\Section\Processor\Inheritance
 */
class PhpArrayTest extends SectionCase
{
    public function testReadsConfig(): void
    {
        $adapter = new PhpArrayAdapter(
            FileSystem::getReaderAdapterRootPath() . 'phpArray' . DIRECTORY_SEPARATOR
        );

        $this->assertSame(
            [
                'source_block1' => [
                    'source_block1Param' => 'source_block1Value'
                ]
            ],
            $adapter->read($this->context)
        );
    }
}