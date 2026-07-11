<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Decoder;

use Ergnuor\SphinxConfig\Domain\ConfigName;
use Ergnuor\SphinxConfig\Input\Declaration\Config;
use Ergnuor\SphinxConfig\Input\Payload\PayloadInterface;
use Ergnuor\SphinxConfig\Schema\ConfigSchemaInterface;

interface DecoderInterface
{
    public function decode(ConfigName $configName, PayloadInterface $payload, ConfigSchemaInterface $schema): Config;
}
