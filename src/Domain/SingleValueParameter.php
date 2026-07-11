<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Domain;

final readonly class SingleValueParameter implements ParameterInterface
{
    public function __construct(
        public ParameterName $name,
        public string $value,
    ) {}
}
