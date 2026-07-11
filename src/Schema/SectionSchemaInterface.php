<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Schema;

interface SectionSchemaInterface
{
    public string $name {get;}
    public function getParameterSchema(string $parameterName): ParameterSchemaInterface;
    public function isMultiBlock(): bool;
}
