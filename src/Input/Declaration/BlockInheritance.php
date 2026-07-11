<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Declaration;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;

final readonly class BlockInheritance
{
    /**
     * @param list<string> $appendValuesFor
     */
    public function __construct(
        public BlockReference $blockReference,
        public array $appendValuesFor = []
    ) {
        foreach ($this->appendValuesFor as $parameterName) {
            // @phpstan-ignore function.alreadyNarrowedType (Defensive runtime guard because PHP cannot enforce iterable value types)
            if (!is_string($parameterName)) {
                throw new InvalidArgumentException('Parameter name must be a string.');
            }
        }
    }
}
