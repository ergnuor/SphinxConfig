<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Domain;

use Ergnuor\SphinxConfig\Support\StringGuard;
use Override;
use Stringable;

abstract readonly class AbstractNotEmptyString implements Stringable
{
    public string $value;

    public function __construct(
        string $value,
        string $nameIsEmptyMessage
    ) {
        $this->value = StringGuard::requireTrimmedNotEmpty(
            $value,
            $nameIsEmptyMessage,
        );
    }

    #[Override]
    public function __toString(): string
    {
        return $this->value;
    }
}
