<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Raw;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;

final readonly class Section
{
    public string $name;

    /** @var array<string, mixed> */
    public array $data;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        string $name,
        array $data,
    ) {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException("Raw section name cannot be empty.");
        }

        $this->name = $name;
        $this->data = $data;
    }
}
