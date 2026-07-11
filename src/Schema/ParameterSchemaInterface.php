<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Schema;

interface ParameterSchemaInterface
{
    public string $name {get;}
    public bool $isMultiValue {get;}
}
