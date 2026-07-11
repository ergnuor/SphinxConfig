<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Exception;

use Throwable;

interface SphinxConfigExceptionInterface extends Throwable
{
    /** @var list<ConfigContext> */
    public array $configContexts {get;}

    public function addOuterConfigContext(ConfigContext $configContext): void;
}
