<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Domain;

use Ergnuor\SphinxConfig\Naming\ParameterName;

final readonly class SingleValueParameter implements ParameterInterface
{
    public function __construct(
        public ParameterName $name,
        public string $value,
    ) {}
}
