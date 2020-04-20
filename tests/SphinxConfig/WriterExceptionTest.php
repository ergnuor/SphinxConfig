<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\Exception\WriterException;

/**
 * @uses \Ergnuor\SphinxConfig\Section\Type
 * @uses \Ergnuor\SphinxConfig\Section\Writer\Adapter
 * @uses \Ergnuor\SphinxConfig\Section
 */
class WriterExceptionTest extends WriterCase
{
    public function setUp()
    {
        $this->setSectionName('unknownSectionType');
        parent::setUp();
    }

    public function testUnknownSectionException()
    {
        $this->expectException(WriterException::class);
        $this->expectExceptionMessage("Unknown section type '{$this->sectionName}'");

        $this->write([]);
    }
}