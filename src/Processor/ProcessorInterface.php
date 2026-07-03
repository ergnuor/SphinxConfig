<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Processor;

use Ergnuor\SphinxConfig\Domain\Config;

interface ProcessorInterface
{
    public function process(Config $config): Config;
}
