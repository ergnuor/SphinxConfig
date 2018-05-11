<?php

use \Ergnuor\SphinxConfig\Tests\TestCase;
use \Ergnuor\SphinxConfig\Section\Source\PhpArray;
use \Ergnuor\SphinxConfig\Exception\SourceException;


class SectionSourcePhpArrayTest extends TestCase
{
    public function testPathRequiredException()
    {
        $this->expectException(SourceException::class);
        $this->expectExceptionMessage('Source path required');

        $source = new PhpArray(null);
    }

    public function testDirectoryDoesNotExstsException()
    {
        $dirPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'unknownDirectory';

        $this->expectException(SourceException::class);
        $this->expectExceptionMessage("Source directory '{$dirPath}' does not exists");

        $source = new PhpArray($dirPath);
    }

    public function testMultiBlockConfigLoad()
    {
        $source = $this->createSource();

        $this->assertSame(
            $source->load('config', 'source'),
            [
                'configInOneFile' => [
                    'sql_query_pre' => [
                        'configInOneFile',
                    ],
                ],

                'sameNameBlock' => [
                    'sql_query_pre' => [
                        'willOverwrite',
                    ],
                ],
            ]
        );
    }

    public function testMultiBlockConfigLoadBlocks()
    {
        $source = $this->createSource();

        $this->assertSame(
            $source->loadBlocks('config', 'source'),
            [
                'blockInOneFile_1' => [
                    'sql_attr_uint' => [
                        'blockInOneFile_1',
                    ],
                ],

                'blockInOneFile_2' => [
                    'sql_attr_uint' => [
                        'blockInOneFile_2',
                    ],
                ],
            ]
        );
    }

    public function testSingleBlockConfigLoad()
    {
        $source = $this->createSource();

        $this->assertSame(
            $source->load('config', 'indexer'),
            [
                'mem_limit' => 1024,
                'sameNameParam' => 'willOverwrite',
                'uniqueParam_1' => true,
                'uniqueParam_2' => true,
            ]
        );
    }

    public function testSingleBlockConfigLoadBlocks()
    {
        $source = $this->createSource();

        $this->assertSame(
            $source->loadBlocks('config', 'indexer'),
            [
                'blockInOneFile_1' => [
                    'sql_attr_uint' => [
                        'blockInOneFile_1',
                    ],
                ],

                'blockInOneFile_2' => [
                    'sql_attr_uint' => [
                        'blockInOneFile_2',
                    ],
                ],
            ]
        );
    }

    protected function createSource()
    {
        $configSource = $this->getConfigRoot() . DIRECTORY_SEPARATOR . 'Source' . DIRECTORY_SEPARATOR . 'PhpArray';
        return new PhpArray($configSource);
    }
}