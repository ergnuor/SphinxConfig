<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\Processor;

use Ergnuor\SphinxConfig\Section\{Context, Processor\Inheritance};
use Ergnuor\SphinxConfig\Tests\Section\SectionCase;

class InheritanceCase extends SectionCase
{
    protected function processInheritance(array $config, Context $readContext = null): array
    {
        return Inheritance::process(
            $this->context,
            $this->normalizeConfig($config, $readContext)
        );
    }
}
