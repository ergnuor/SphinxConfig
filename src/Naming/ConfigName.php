<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Naming;

final readonly class ConfigName extends AbstractNotEmptyString
{
    public function __construct(
        string $value
    ) {
        parent::__construct(
            $value,
            'Config name cannot be empty.'
        );
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
