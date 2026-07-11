<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Declaration;

use Ergnuor\SphinxConfig\Support\StringGuard;

final readonly class PlaceholderValue
{
    public string $placeholder;

    public function __construct(
        string $placeholder,
        public string $value,
    ) {
        $this->placeholder = StringGuard::requireTrimmedNotEmpty(
            $placeholder,
            'Placeholder cannot be empty.'
        );
    }
}
