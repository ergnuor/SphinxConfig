<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input;

use Ergnuor\SphinxConfig\Domain\Config;
use Ergnuor\SphinxConfig\Input\Raw\Config as RawConfig;

final class Normalizer
{
    public function normalize(RawConfig $rawConfig): Config
    {
        return new Config($rawConfig->name);
    }
}
