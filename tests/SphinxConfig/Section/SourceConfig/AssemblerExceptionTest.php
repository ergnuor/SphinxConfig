<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\SourceConfig;

use Ergnuor\SphinxConfig\Exception\{Message as ExceptionMessage, SectionException};

class AssemblerExceptionTest extends AssemblerCase
{
    public function testExternalBlockDoesNotExistsException(): void
    {
        $externalConfigName = 'externalConfig_1';
        $externalBlockName = 'level2';

        $this->expectException(SectionException::class);
        $this->expectExceptionMessage(
            ExceptionMessage::forContext(
                $this->context,
                "Block '{$externalConfigName}@{$externalBlockName}' does not exists"
            )
        );

        $this->readerAdapterStub->setSourceData(
            [
                $this->context->getConfigName() => [
                    $this->context->getSectionType() => [
                        'level1' => [
                            'extends' => "{$externalConfigName}@{$externalBlockName}",
                        ],
                    ],
                ],
                'externalConfig_1' => [
                ],
            ]
        );

        $this->assembler->assemble($this->context);
    }

    public function testNameConflictException(): void
    {
        $externalConfigName = 'externalConfig_1';
        $externalBlockName = 'level1';

        $this->expectException(SectionException::class);
        $this->expectExceptionMessage(
            ExceptionMessage::forContext(
                $this->context,
                "There is a name conflict while pulling external block '{$externalConfigName}@{$externalBlockName}'." .
                " Block with that name already exists: '{$this->context->getConfigName()}@level1'."
            )
        );

        $this->readerAdapterStub->setSourceData(
            [
                $this->context->getConfigName() => [
                    $this->context->getSectionType() => [
                        'level1' => [
                            'extends' => "{$externalConfigName}@{$externalBlockName}",
                        ],
                    ],
                ],
                $externalConfigName => [
                    $this->context->getSectionType() => [
                        $externalBlockName => [
                        ],
                    ],
                ],
            ]
        );

        $this->assembler->assemble($this->context);
    }
}
