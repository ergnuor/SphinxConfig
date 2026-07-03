<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Domain;

final class Config
{
    public function __construct(
        public readonly ConfigName $name,
    ) {}
}
