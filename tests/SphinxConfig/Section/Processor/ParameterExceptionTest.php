<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\Processor;

use Ergnuor\SphinxConfig\Exception\{Message as ExceptionMessage, SectionException};

class ParameterExceptionTest extends ParameterCase
{
    public function testCircularPlaceholderException(): void
    {
        $this->expectException(SectionException::class);
        $this->expectExceptionMessage(
            ExceptionMessage::forContext(
                $this->context,
                "Circular placeholders detected. Processed placeholders: " .
                implode(', ', ['::placeholder::', '::anotherPlaceholder::', '::yetAnotherPlaceholder::',]) .
                ". Next placeholders: " .
                implode(', ', ['::placeholder::'])
            )
        );

        $this->processParameter(
            [
                'level1' => [
                    'param' => '::placeholder::',
                    'placeholderValues' => [
                        'placeholder' => 'value with ::anotherPlaceholder::',
                        'anotherPlaceholder' => 'value with ::yetAnotherPlaceholder::',
                        'yetAnotherPlaceholder' => 'value with ::placeholder::',
                    ]
                ]
            ]
        );
    }
}
