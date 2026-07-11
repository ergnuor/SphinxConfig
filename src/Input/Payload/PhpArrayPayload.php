<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Payload;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;
use Ergnuor\SphinxConfig\Input\Payload\PhpArray\FragmentInterface;

final readonly class PhpArrayPayload implements PayloadInterface
{
    /**
     * @param list<FragmentInterface> $fragments
     */
    public function __construct(
        public array $fragments
    ) {
        foreach ($this->fragments as $fragment) {
            // @phpstan-ignore instanceof.alwaysTrue (Defensive runtime guard because PHP cannot enforce iterable value types)
            if (!($fragment instanceof FragmentInterface)) {
                throw new InvalidArgumentException("Fragment must be an instance of '" . FragmentInterface::class . "'.");
            }
        }
    }
}
