<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfigOld\Section\Reader;

interface Transformer
{
    public function afterRead(array $config): array;

    public function afterAddSeparateBlocks(array $config): array;
}