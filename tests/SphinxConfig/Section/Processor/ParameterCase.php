<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Tests\Section\Processor;

use Ergnuor\SphinxConfig\Section\Processor\Parameter;
use Ergnuor\SphinxConfig\Tests\Section\SectionCase;

class ParameterCase extends SectionCase
{
    protected function processParameter(array $config, array $globalPlaceholders = []): array
    {
        return Parameter::process(
            $this->context,
            $this->normalizeConfig($config),
            $globalPlaceholders
        );
    }
}
