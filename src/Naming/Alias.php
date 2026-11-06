<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Naming;

final readonly class Alias extends AbstractNotEmptyString
{
    public function __construct(
        string $value
    ) {
        parent::__construct(
            $value,
            'Alias cannot be empty.'
        );
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
