<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Domain;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;

final readonly class MultiValueParameter implements ParameterInterface
{
    /**
     * @param list<AliasedValue> $values
     */
    public function __construct(
        public ParameterName $name,
        public array $values,
    ) {
        $seenAliases = [];
        foreach ($this->values as $value) {
            // @phpstan-ignore instanceof.alwaysTrue (Defensive runtime guard because PHP cannot enforce iterable value types)
            if (!($value instanceof AliasedValue)) {
                throw new InvalidArgumentException("Multi-value parameter value must be an instance of '" . AliasedValue::class . "'.");
            }

            if ($value->alias !== null) {
                if (array_key_exists($value->alias, $seenAliases)) {
                    throw new InvalidArgumentException("Duplicate alias '$value->alias' for multi-value parameter value.");
                }

                $seenAliases[$value->alias] = true;
            }
        }
    }
}
