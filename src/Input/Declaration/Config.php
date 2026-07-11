<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Input\Declaration;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;

final readonly class Config
{
    /**
     * @param list<SectionBlock> $sectionBlocks
     */
    public function __construct(public array $sectionBlocks)
    {
        foreach ($this->sectionBlocks as $section) {
            // @phpstan-ignore instanceof.alwaysTrue (Defensive runtime guard because PHP cannot enforce iterable value types)
            if (!($section instanceof SectionBlock)) {
                throw new InvalidArgumentException("Section block must be an instance of '" . SectionBlock::class . "'.");
            }
        }
    }
}
