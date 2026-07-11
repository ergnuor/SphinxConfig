<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Domain;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;

final readonly class BlockInheritance
{
    /**
     * @param list<ParameterName> $appendValuesFor
     */
    public function __construct(
        public BlockReference $blockReference,
        public array $appendValuesFor = []
    ) {
        $seenParameterNames = [];
        foreach ($this->appendValuesFor as $parameterName) {
            // @phpstan-ignore instanceof.alwaysTrue (Defensive runtime guard because PHP cannot enforce iterable value types)
            if (!($parameterName instanceof ParameterName)) {
                throw new InvalidArgumentException("Parameter name must be an instance of '" . ParameterName::class . "'.");
            }

            $key = (string) $parameterName;

            if (array_key_exists($key, $seenParameterNames)) {
                throw new InvalidArgumentException("Duplicate parameter name '$parameterName'.");
            }

            $seenParameterNames[$key] = true;
        }
    }
}
