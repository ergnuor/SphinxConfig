<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Domain;

use Ergnuor\SphinxConfig\Support\StringGuard;

final readonly class AliasedValue
{
    public ?string $alias;

    public function __construct(
        public string $value,
        ?string $alias = null,
    ) {
        if ($alias !== null) {
            $alias = StringGuard::requireTrimmedNotEmpty(
                $alias,
                'Alias cannot be empty.'
            );
        }
        $this->alias = $alias;
    }
}
