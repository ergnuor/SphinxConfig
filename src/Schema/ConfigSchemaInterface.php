<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Schema;

interface ConfigSchemaInterface
{
    public function getSectionSchema(string $sectionName): SectionSchemaInterface;
}
