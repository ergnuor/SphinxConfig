<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Resolver;

use Ergnuor\SphinxConfig\Domain\Config;
use Ergnuor\SphinxConfig\Exception\LogicException;
use Ergnuor\SphinxConfig\Input\ConfigImporter;

final readonly class ExternalBlockResolver
{
    public function resolve(Config $config, ConfigImporter $configImporter): Config
    {
        throw new LogicException('Not implemented.');
    }
}
