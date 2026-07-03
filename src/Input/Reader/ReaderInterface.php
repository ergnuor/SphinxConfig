<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Reader;

use Ergnuor\SphinxConfig\Domain\ConfigName;
use Ergnuor\SphinxConfig\Input\Payload\PayloadInterface;

interface ReaderInterface
{
    public function read(ConfigName $configName): PayloadInterface;
}
