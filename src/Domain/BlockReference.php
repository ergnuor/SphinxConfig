<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Domain;

use Ergnuor\SphinxConfig\Naming\BlockName;
use Ergnuor\SphinxConfig\Naming\ConfigName;

final readonly class BlockReference
{
    public function __construct(
        public BlockName $blockName,
        public ConfigName $configName,
    ) {}
}
