<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\Section\Reader\Adapter\File\PhpArray as PhpArrayAdapter;

/**
 * @uses \Ergnuor\SphinxConfig\Section\Reader\Adapter\File
 * @uses \Ergnuor\SphinxConfig\Section
 */
class ReaderAdapterPhpArrayTest extends SectionCase
{
    public function testReadsConfig(): void
    {
        $this->setUpConfigEnvironment('whatever', 'config', 'section1');

        $adapter = new PhpArrayAdapter(
            FileSystem::getReaderAdapterRootPath() . 'phpArray' . DIRECTORY_SEPARATOR
        );

        $this->assertSame(
            [
                'section1_block1' => [
                    'section1_block1Param' => 'section1_block1Value'
                ]
            ],
            $adapter->readConfig(
                $this->configNameToRead,
                $this->sectionParameterObject
            )
        );
    }
}