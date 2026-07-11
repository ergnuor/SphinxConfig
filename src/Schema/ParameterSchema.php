<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Schema;

use Ergnuor\SphinxConfig\Support\StringGuard;

final readonly class ParameterSchema implements ParameterSchemaInterface
{
    public string $name;

    public function __construct(
        string $name,
        public bool $isMultiValue,
    ) {
        $this->name = StringGuard::requireTrimmedNotEmpty(
            $name,
            'Parameter name cannot be empty.',
        );
    }
}
