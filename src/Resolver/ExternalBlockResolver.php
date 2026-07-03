<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Resolver;

use Ergnuor\SphinxConfig\Domain\Config;
//use Ergnuor\SphinxConfig\Input\ConfigImporter;
use Ergnuor\SphinxConfig\Exception\LogicException;

final readonly class ExternalBlockResolver
{
    //    public function __construct(
    //        private ConfigImporter $configImporter,
    //    ) {
    //    }

    public function resolve(Config $config): Config
    {
        throw new LogicException('Not implemented');
    }
}
