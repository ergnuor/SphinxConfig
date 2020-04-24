<?php

namespace Ergnuor\SphinxConfig\Section;

use Ergnuor\SphinxConfig\{
    Section,
    Section\Reader\Adapter as ReaderAdapter
};

class Reader
{
    /**
     * @var ReaderAdapter
     */
    private $readerAdapter;

    public function __construct(ReaderAdapter $readerAdapter)
    {
        $this->readerAdapter = $readerAdapter;
    }

    final public function readConfig(string $configName, Section $section): array
    {
        $config = $this->readerAdapter->readConfig($configName, $section);

        if (Type::isSingleBlock($section->getName())) {
            $config = $this->transformToMultiBlockSection($config, $section);
        }

        $config = array_replace(
            $config,
            $this->readerAdapter->readConfigBlocks($configName, $section)
        );

        if (Type::isSingleBlock($section->getName())) {
            $config = $this->transformBlocksToPseudo($configName, $config, $section);
        }

        return $config;
    }

    /**
     * Unlike sections 'source' and 'index',
     * the real 'indexer', 'searchd' and 'common' sections (single block sections) do not contain blocks.
     * But internally they are expected to have the same structure, so we transform them into a sections with a block.
     * The block name is equal to the section name.
     * @param array $config
     * @param Section $section
     * @return array
     */
    private function transformToMultiBlockSection(array $config, Section $section): array
    {
        $sectionName = $section->getName();

        if ($this->isAlreadyHaveSection($config, $sectionName)) {
            return $config;
        }

        return [
            $sectionName => $config,
        ];
    }

    private function isAlreadyHaveSection(array $config, string $sectionName): bool
    {
        return (
            isset($config[$sectionName]) &&
            is_array($config[$sectionName])
        );
    }

    /**
     * Unlike sections 'source' and 'index',
     * the real 'indexer', 'searchd' and 'common' sections (single block sections) do not contain blocks.
     * But internally they are expected to have the same structure.
     * When inheriting, we want all the values of parent blocks to be copied to the main block
     * (the block which name is equal to the section name),
     * due to only this block will be included in the final configuration for the single block sections.
     * So we mark parent blocks as pseudo.
     * @param string $configName
     * @param $config
     * @param Section $section
     * @return array
     */
    private function transformBlocksToPseudo(string $configName, $config, Section $section): array
    {
        array_walk(
            $config,
            function (&$blockConfig, $blockName) use ($section, $configName) {
                if (
                    $configName != $section->getConfigName() ||
                    $blockName != $section->getName()
                ) {
                    $blockConfig['isPseudo'] = true;
                }
            }
        );

        return $config;
    }
}