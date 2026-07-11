<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Domain;

final readonly class ParameterName extends AbstractNotEmptyString
{
    public function __construct(
        string $value
    ) {
        parent::__construct(
            $value,
            'Parameter name cannot be empty.'
        );
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
