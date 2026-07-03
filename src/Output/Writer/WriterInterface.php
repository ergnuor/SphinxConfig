<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Output\Writer;

use Ergnuor\SphinxConfig\Domain\ConfigName;

interface WriterInterface
{
    public function write(ConfigName $configName, string $contents): void;
}
