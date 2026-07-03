<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Decoder;

use Ergnuor\SphinxConfig\Domain\ConfigName;
use Ergnuor\SphinxConfig\Input\Payload\PayloadInterface;
use Ergnuor\SphinxConfig\Input\Raw\Config;

interface DecoderInterface
{
    /**
     * Decodes payload as config identified by $configName.
     * Returned Raw\Config must have the same name.
     */
    public function decode(ConfigName $configName, PayloadInterface $payload): Config;
}
