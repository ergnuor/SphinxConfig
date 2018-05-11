<?php

namespace Ergnuor\SphinxConfig\Section;

/*
 * Handles sections that do not contain blocks. Such as 'indexer', 'searchd' and 'common'.
 */

class SingleBlock extends MultiBlock
{

    /**
     * Loads the specified configuration from the source
     *
     * @param string $configName
     */
    protected function loadConfig($configName)
    {
        parent::loadConfig($configName);

        /*
         * When inheriting, we want all the values of parent blocks to be copied to the main block
         * (The block which name is equal to the section name)
         * So we mark them as pseudo.
         */
        $this->configs[$configName] = array_map(
            function ($blockConfig) use ($configName) {
                if (
                    $configName != $this->configName ||
                    $blockConfig['name'] != $this->sectionName
                ) {
                    $blockConfig['isPseudo'] = true;
                }

                return $blockConfig;
            },
            $this->configs[$configName]
        );
    }

    /**
     * @param string $configName
     * @return array
     */
    protected function loadMainConfig($configName)
    {
        $sectionConfig = parent::loadMainConfig($configName);

        /*
         * The 'indexer', 'searchd' and 'common' sections do not contain blocks.
         * For consistency, we transform it into a section with a block.
         * The block name is equal to the section name.
         */
        if (!empty($sectionConfig)) {
            $sectionConfig = [
                $this->sectionName => $sectionConfig,
            ];
        }

        return $sectionConfig;
    }

    /**
     * Cleans config from internal parameters
     *
     * @return array
     */
    protected function getCleanConfig()
    {
        $cleanConfig = parent::getCleanConfig();
        return isset($cleanConfig[$this->sectionName]['config']) ? $cleanConfig[$this->sectionName]['config'] : [];
    }

    protected function getCleanBlock($blockConfig)
    {
        return array_diff_key(
            parent::getCleanBlock($blockConfig),
            array_flip(
                [
                    'extends',
                ]
            )
        );
    }
}