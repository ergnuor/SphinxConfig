<?php

namespace Ergnuor\SphinxConfig\Tests;

use Ergnuor\SphinxConfig\Exception\WriterException;

/**
 * @uses \Ergnuor\SphinxConfig\Section
 */
class WriterExceptionTest extends WriterCase
{
    public function setUp(): void
    {
        $this->setSectionName('unknownSectionType');
        parent::setUp();
    }

    public function testUnknownSectionException(): void
    {
        $this->expectException(WriterException::class);
        $this->expectExceptionMessage("Unknown section type '{$this->sectionName}'");

        $this->write([]);
    }
}