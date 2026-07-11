<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Schema;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;
use Ergnuor\SphinxConfig\Support\StringGuard;
use Override;

final readonly class SectionSchema implements SectionSchemaInterface
{
    public string $name;
    /** @var array<string, ParameterSchemaInterface> */
    private array $parameterSchemasByName;

    /**
     * @param list<ParameterSchemaInterface> $parameterSchemas
     */
    public function __construct(
        string $name,
        private SectionBlockMode $blockMode,
        array $parameterSchemas
    ) {
        $this->name = StringGuard::requireTrimmedNotEmpty(
            $name,
            'Section name cannot be empty.',
        );

        $parameterSchemasByName = [];
        foreach ($parameterSchemas as $parameterSchema) {
            // @phpstan-ignore instanceof.alwaysTrue (Defensive runtime guard because PHP cannot enforce iterable value types)
            if (!($parameterSchema instanceof ParameterSchemaInterface)) {
                throw new InvalidArgumentException("Parameter schema must be an instance of '" . ParameterSchemaInterface::class . "'.");
            }

            $key = $parameterSchema->name;

            if (array_key_exists($key, $parameterSchemasByName)) {
                throw new InvalidArgumentException("Duplicate parameter schema '$parameterSchema->name'.");
            }

            $parameterSchemasByName[$key] = $parameterSchema;
        }

        $this->parameterSchemasByName = $parameterSchemasByName;
    }

    #[Override]
    public function getParameterSchema(string $parameterName): ParameterSchemaInterface
    {
        if (!array_key_exists($parameterName, $this->parameterSchemasByName)) {
            return new ParameterSchema(
                $parameterName,
                false
            );
        }

        return $this->parameterSchemasByName[$parameterName];
    }

    #[Override]
    public function isMultiBlock(): bool
    {
        return $this->blockMode === SectionBlockMode::Multi;
    }
}
