<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Domain;

final readonly class BlockName extends AbstractNotEmptyString
{
    public function __construct(
        string $value
    ) {
        parent::__construct(
            $value,
            'Block name cannot be empty.'
        );
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
