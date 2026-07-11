<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Payload\PhpArray;

/**
 * @phpstan-type PhpArrayConfigParameterValueType string|array<array-key, string>
 *
 * @phpstan-type PhpArraySectionBlockConfigType array{
 *     config?: array<string, PhpArrayConfigParameterValueType>,
 *     isTemplate?: bool,
 *     inheritance?: array{
 *          extends: string,
 *          appendValuesFor?: list<string>,
 *     },
 *     placeholderValues?: array<string, string>,
 * }
 *
 * @phpstan-type PhpArraySectionConfigType PhpArraySectionBlockConfigType|array<string, PhpArraySectionBlockConfigType>
 *
 * @phpstan-type PhpArrayWholeConfigType array<string, PhpArraySectionConfigType>
 */
interface FragmentInterface {}
