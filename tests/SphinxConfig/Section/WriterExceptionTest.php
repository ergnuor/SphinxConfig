<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section;

use Ergnuor\SphinxConfig\Exception\{Message as ExceptionMessage, WriterException};

/**
 * @uses \Ergnuor\SphinxConfig\Section\Processor\Inheritance
 */
class WriterExceptionTest extends WriterCase
{
    public function testUnknownSectionException(): void
    {
        $this->setSectionType('unknownSectionType');
        $this->expectException(WriterException::class);
        $this->expectExceptionMessage(
            ExceptionMessage::forContext($this->context, 'unknown section type')
        );

        $this->write([]);
    }
}