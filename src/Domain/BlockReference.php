<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Domain;

final readonly class BlockReference
{
    public function __construct(
        public BlockName $blockName,
        public ConfigName $configName,
    ) {}
}
