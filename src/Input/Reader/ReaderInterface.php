<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Reader;

use Ergnuor\SphinxConfig\Input\Payload\PayloadInterface;
use Ergnuor\SphinxConfig\Naming\ConfigName;

interface ReaderInterface
{
    public function read(ConfigName $configName): PayloadInterface;
}
