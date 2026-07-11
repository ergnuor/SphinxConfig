<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Domain;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;

final readonly class Block
{
    /** @var array<string, ParameterInterface> */
    public array $parameters;

    /**
     * @param list<ParameterInterface> $parameters
     * @param list<PlaceholderValue> $placeholderValues
     */
    public function __construct(
        public BlockName $name,
        array $parameters,
        public bool $isTemplate,
        public ?BlockInheritance $inheritance = null,
        public array $placeholderValues = [],
    ) {
        $parametersByName = [];

        foreach ($parameters as $value) {
            // @phpstan-ignore instanceof.alwaysTrue (Defensive runtime guard because PHP cannot enforce iterable value types)
            if (!($value instanceof ParameterInterface)) {
                throw new InvalidArgumentException("Parameter must be an instance of '" . ParameterInterface::class . "'.");
            }

            $key = (string) $value->name;

            if (array_key_exists($key, $parametersByName)) {
                throw new InvalidArgumentException("Duplicate parameter name: '$key'.");
            }

            $parametersByName[$key] = $value;
        }

        $this->parameters = $parametersByName;

        $seenPlaceholderNames = [];
        foreach ($this->placeholderValues as $value) {
            // @phpstan-ignore instanceof.alwaysTrue (Defensive runtime guard because PHP cannot enforce iterable value types)
            if (!($value instanceof PlaceholderValue)) {
                throw new InvalidArgumentException("Placeholder values item must be an instance of '" . PlaceholderValue::class . "'.");
            }

            if (array_key_exists($value->placeholder, $seenPlaceholderNames)) {
                throw new InvalidArgumentException("Duplicate placeholder name: '$value->placeholder'.");
            }

            $seenPlaceholderNames[$value->placeholder] = true;
        }
    }
}
