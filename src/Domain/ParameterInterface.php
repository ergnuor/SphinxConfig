<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Domain;

use Ergnuor\SphinxConfig\Naming\ParameterName;

interface ParameterInterface
{
    public ParameterName $name {get;}
}
