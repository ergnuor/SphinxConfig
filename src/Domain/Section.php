<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Domain;

use Ergnuor\SphinxConfig\Exception\InvalidArgumentException;

final readonly class Section
{
    private const int SINGLE_BLOCK_SECTION_MAX_NON_TEMPLATE_BLOCKS_COUNT = 1;

    /** @var array<string, Block> */
    public array $blocks;

    /**
     * @param list<Block> $blocks
     */
    public function __construct(
        public SectionName $name,
        public bool $isMultiBlock,
        array $blocks,
    ) {
        $blocksByName = [];

        $nonTemplateBlockCount = 0;
        foreach ($blocks as $block) {
            // @phpstan-ignore instanceof.alwaysTrue (Defensive runtime guard because PHP cannot enforce iterable value types)
            if (!($block instanceof Block)) {
                throw new InvalidArgumentException("Block must be an instance of '" . Block::class . "'.");
            }

            $key = (string) $block->name;

            if (array_key_exists($key, $blocksByName)) {
                throw new InvalidArgumentException("Duplicate block name '$key'.");
            }

            if (!$block->isTemplate) {
                $nonTemplateBlockCount++;
            }

            if (
                !$isMultiBlock
                && $nonTemplateBlockCount > self::SINGLE_BLOCK_SECTION_MAX_NON_TEMPLATE_BLOCKS_COUNT
            ) {
                throw new InvalidArgumentException('Single-block section cannot have more than ' . self::SINGLE_BLOCK_SECTION_MAX_NON_TEMPLATE_BLOCKS_COUNT . ' non-template blocks.');
            }

            $blocksByName[$key] = $block;
        }

        $this->blocks = $blocksByName;
    }
}
