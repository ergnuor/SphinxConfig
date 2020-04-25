<?php

namespace Ergnuor\SphinxConfig\Tests\Section;

use Ergnuor\SphinxConfig\Exception\WriterException;
use Ergnuor\SphinxConfig\Tests\TestCase\WriterCase;

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