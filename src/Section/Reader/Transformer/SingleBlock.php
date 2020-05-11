<?php

declare(strict_types=1);

namespace Ergnuor\SphinxConfig\Section\Reader\Transformer;

class SingleBlock extends MultiBlock
{
    public function afterRead(array $config): array
    {
        return $this->transformToMultiBlockSection($config);
    }

    /**
     * Unlike sections 'source' and 'index',
     * the real 'indexer', 'searchd' and 'common' sections (single block sections) do not contain blocks.
     * But internally they are expected to have the same structure, so we transform them into a sections with a block.
     * The block name is equal to the section name.
     * @param array $config
     * @return array
     */
    private function transformToMultiBlockSection(array $config): array
    {
        $sectionType = $this->context->getSectionType();

        if ($this->isAlreadyHaveSection($config, $sectionType)) {
            return $config;
        }

        return [
            $sectionType => $config,
        ];
    }


    private function isAlreadyHaveSection(array $config, string $sectionType): bool
    {
        return (
            isset($config[$sectionType]) &&
            is_array($config[$sectionType])
        );
    }

    public function afterAddSeparateBlocks(array $config): array
    {
        return $this->transformBlocksToPseudo($config);
    }

    /**
     * Unlike sections 'source' and 'index',
     * the real 'indexer', 'searchd' and 'common' sections (single block sections) do not contain blocks.
     * But internally they are expected to have the same structure.
     * When inheriting, we want all the values of parent blocks to be copied to the main block
     * (the block which name is equal to the section name),
     * due to only this block will be included in the final configuration for the single block sections.
     * So we mark parent blocks as pseudo.
     * @param array $config
     * @return array
     */
    private function transformBlocksToPseudo(array $config): array
    {
        array_walk(
            $config,
            function (&$block, $blockName) {
                if ($blockName != $this->context->getSectionType()) {
                    $block['isPseudo'] = true;
                }
            }
        );

        return $config;
    }
}