<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Domain;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;

final readonly class ConfigName
{
    public string $value;

    public function __construct(
        string $name
    ) {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException('Config name cannot be empty');
        }

        $this->value = $name;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
