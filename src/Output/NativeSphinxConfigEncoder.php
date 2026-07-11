<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Output;

use Ergnuor\SphinxConfig\Domain\Config;
use Ergnuor\SphinxConfig\Exception\LogicException;

final class NativeSphinxConfigEncoder
{
    public function encode(Config $config): string
    {
        throw new LogicException('Not implemented.');
    }
}
