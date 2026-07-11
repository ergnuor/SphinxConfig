<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Payload\PhpArray;

/**
 * @phpstan-import-type PhpArrayWholeConfigType from FragmentInterface
 */
final readonly class ConfigFragment implements FragmentInterface
{
    /**
     * @param PhpArrayWholeConfigType $data
     */
    public function __construct(
        public array $data
    ) {}
}
