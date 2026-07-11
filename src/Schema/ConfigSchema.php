<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Schema;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;
use Override;

final readonly class ConfigSchema implements ConfigSchemaInterface
{
    /** @var array<string, SectionSchemaInterface> */
    private array $sectionSchemasByName;

    /**
     * @param list<SectionSchemaInterface> $sectionSchemas
     */
    public function __construct(array $sectionSchemas)
    {
        $sectionSchemasByName = [];
        foreach ($sectionSchemas as $sectionSchema) {
            // @phpstan-ignore instanceof.alwaysTrue (Defensive runtime guard because PHP cannot enforce iterable value types)
            if (!($sectionSchema instanceof SectionSchemaInterface)) {
                throw new InvalidArgumentException("Section schema must be an instance of '" . SectionSchemaInterface::class . "'.");
            }

            if (array_key_exists($sectionSchema->name, $sectionSchemasByName)) {
                throw new InvalidArgumentException("Duplicate section schema '$sectionSchema->name'.");
            }

            $sectionSchemasByName[$sectionSchema->name] = $sectionSchema;
        }

        $this->sectionSchemasByName = $sectionSchemasByName;
    }

    #[Override]
    public function getSectionSchema(string $sectionName): SectionSchemaInterface
    {
        if (!array_key_exists($sectionName, $this->sectionSchemasByName)) {
            throw new InvalidArgumentException("Section schema '$sectionName' does not exist.");
        }

        return $this->sectionSchemasByName[$sectionName];
    }
}
