<?php

namespace Ergnuor\SphinxConfig\Section;


use Ergnuor\SphinxConfig\Section;

class Reader
{
    /**
     * @var Reader\Adapter
     */
    private $readerAdapter;

    public function __construct(Reader\Adapter $readerAdapter)
    {
        $this->readerAdapter = $readerAdapter;
    }

    /**
     * @param string $configName
     * @param Section $section
     * @return array
     */
    final public function readConfig($configName, Section $section)
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
     *
     * @param $config
     * @param Section $section
     * @return array
     */
    private function transformToMultiBlockSection($config, Section $section)
    {
        $sectionName = $section->getName();

        if (
            isset($config[$sectionName]) &&
            is_array($config[$sectionName])
        ) {
            return $config;
        }

        return [
            $sectionName => $config,
        ];
    }

    /**
     * Unlike sections 'source' and 'index',
     * the real 'indexer', 'searchd' and 'common' sections (single block sections) do not contain blocks.
     * But internally they are expected to have the same structure.
     * When inheriting, we want all the values of parent blocks to be copied to the main block
     * (the block which name is equal to the section name),
     * due to only this block will be included in the final configuration for the single block sections.
     * So we mark parent blocks as pseudo.
     *
     * @param $configName
     * @param $config
     * @param Section $section
     * @return array
     */
    private function transformBlocksToPseudo($configName, $config, Section $section)
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