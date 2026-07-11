<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Declaration;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;
use Ergnuor\SphinxConfig\Support\StringGuard;

final readonly class SectionBlock
{
    public string $sectionName;
    public string $blockName;

    /**
     * @param list<Parameter> $parameters
     * @param list<PlaceholderValue> $placeholderValues
     */
    public function __construct(
        string $sectionName,
        string $blockName,
        public array $parameters,
        public bool $isTemplate = false,
        public ?BlockInheritance $inheritance = null,
        public array $placeholderValues = []
    ) {
        $this->sectionName = StringGuard::requireTrimmedNotEmpty(
            $sectionName,
            'Section name cannot be empty.'
        );

        $this->blockName = StringGuard::requireTrimmedNotEmpty(
            $blockName,
            'Block name cannot be empty.'
        );

        foreach ($this->parameters as $parameter) {
            // @phpstan-ignore instanceof.alwaysTrue (Defensive runtime guard because PHP cannot enforce iterable value types)
            if (!($parameter instanceof Parameter)) {
                throw new InvalidArgumentException("Parameter must be an instance of '" . Parameter::class . "'.");
            }
        }

        foreach ($this->placeholderValues as $placeholderValue) {
            // @phpstan-ignore instanceof.alwaysTrue (Defensive runtime guard because PHP cannot enforce iterable value types)
            if (!($placeholderValue instanceof PlaceholderValue)) {
                throw new InvalidArgumentException("Placeholder value must be an instance of '" . PlaceholderValue::class . "'.");
            }
        }
    }
}
