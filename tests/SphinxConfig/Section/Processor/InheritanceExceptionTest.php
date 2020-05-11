<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\Processor;

use Ergnuor\SphinxConfig\Exception\{Message as ExceptionMessage, SectionException};

class InheritanceExceptionTest extends InheritanceCase
{
    public function testCircularInheritanceException(): void
    {
        $readContext = $this->createContext('readConfigName');
        $configName = $readContext->getConfigName();

        $this->expectException(SectionException::class);
        $this->expectExceptionMessage(
            ExceptionMessage::forContext(
                $this->context,
                'Circular inheritance detected. Inheritance path: ' .
                implode(
                    ' -> ',
                    [
                        "{$configName}@block1",
                        "{$configName}@block2",
                        "{$configName}@block3",
                        "{$configName}@block1",
                    ]
                )
            )
        );

        $this->processInheritance(
            [
                'block1' => ['extends' => 'block2',],
                'block2' => ['extends' => 'block3',],
                'block3' => ['extends' => 'block1',],
            ],
            $readContext
        );
    }

    public function testParentBlockDoesNotExistsException(): void
    {
        $this->expectException(SectionException::class);
        $this->expectExceptionMessage(
            ExceptionMessage::forContext(
                $this->context,
                "Parent block '{$this->context->getConfigName()}@block3' does not exists"
            )
        );

        $this->processInheritance(
            [
                'block1' => ['extends' => 'block2',],
                'block2' => ['extends' => 'block3',],
            ]
        );
    }
}
