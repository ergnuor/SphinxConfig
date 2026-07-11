<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Declaration;

use Ergnuor\SphinxConfig\Support\StringGuard;

final readonly class Parameter
{
    public string $name;
    public ?string $alias;

    public function __construct(
        string $name,
        public string $value,
        ?string $alias = null
    ) {
        $this->name = StringGuard::requireTrimmedNotEmpty(
            $name,
            'Parameter name cannot be empty.'
        );

        if ($alias !== null) {
            $alias = StringGuard::requireTrimmedNotEmpty(
                $alias,
                'Parameter alias cannot be empty.'
            );
        }
        $this->alias = $alias;
    }
}
