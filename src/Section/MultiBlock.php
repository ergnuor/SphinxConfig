<?php

namespace Ergnuor\SphinxConfig\Section;


use \Ergnuor\SphinxConfig;
use \Ergnuor\SphinxConfig\Exception\SectionException;

/*
 * Handles sections that contain blocks. Such as 'source' and 'index'
 */

class MultiBlock
{
    /**
     * Contains the current and external configurations of the section
     *
     * @var array
     */
    protected $configs = [];

    /**
     * Contains current config name
     *
     * @var null|string
     */
    protected $configName = null;

    /**
     * @var null|string
     */
    protected $sectionName = null;

    /**
     * @var null|\Ergnuor\SphinxConfig\SphinxConfigAbstract
     */
    protected $sphinxConfig = null;

    /**
     * List of sphinx multi-value parameters
     *
     * @var array
     */
    protected $multiValueParamList = [
        'sql_query_pre',
        'sql_joined_field',
        'sql_attr_uint',
        'sql_attr_bool',
        'sql_attr_bigint',
        'sql_attr_timestamp',
        'sql_attr_float',
        'sql_attr_multi',
        'sql_attr_string',
        'sql_attr_json',
        'sql_field_string',
        'xmlpipe_field',
        'xmlpipe_field_string',
        'xmlpipe_attr_uint',
        'xmlpipe_attr_bigint',
        'xmlpipe_attr_bool',
        'xmlpipe_attr_timestamp',
        'xmlpipe_attr_float',
        'xmlpipe_attr_multi',
        'xmlpipe_attr_multi_64',
        'xmlpipe_attr_string',
        'xmlpipe_attr_json',
        'unpack_zlib',
        'unpack_mysqlcompress',
        'source',
        'local',
        'agent',
        'agent_persistent',
        'agent_blackhole',
        'rt_field',
        'rt_attr_uint',
        'rt_attr_bool',
        'rt_attr_bigint',
        'rt_attr_float',
        'rt_attr_multi',
        'rt_attr_multi_64',
        'rt_attr_timestamp',
        'rt_attr_string',
        'rt_attr_json',
        'regexp_filter',
    ];

    /**
     * List of non sphinx parameters.
     * Used at a specific stage to separate the sphinx parameters from others
     *
     * @var array
     */
    protected $systemParamsList = [
        'extends',
        'extendsConfig',
        'config',
        'placeholderValues',
        'isPseudo',
    ];

    /**
     * An object to load the source config
     *
     * @var SourceInterface|null
     */
    protected $source = null;

    /**
     * @param string $configName
     * @param string $sectionName
     * @param SourceInterface $source
     * @param \Ergnuor\SphinxConfig\SphinxConfigAbstract $sphinxConfig
     */
    public function __construct(
        $configName,
        $sectionName,
        SourceInterface $source,
        SphinxConfig\SphinxConfigAbstract $sphinxConfig
    ) {
        $this->configName = (string)$configName;
        $this->sectionName = (string)$sectionName;
        $this->source = $source;
        $this->sphinxConfig = $sphinxConfig;

        $this->configs[$configName] = [];
    }

    /**
     * Returns the assembled config
     *
     * @return array
     * @throws SectionException
     */
    public function getConfig()
    {
        $this->loadConfig($this->configName);
        $this->assemble();

        /*var_dump($this->sectionName);
        var_dump($this->configs);
        var_dump('-----------');*/
//        exit;

        return $this->getCleanConfig();
    }


    /**
     * @return null|string
     */
    public function getSectionName()
    {
        return $this->sectionName;
    }

    /**
     * Cleans config from internal parameters
     *
     * @return array
     */
    protected function getCleanConfig()
    {
        $cleanConfig = [];
        foreach ($this->configs[$this->configName] as $blockName => $blockConfig) {
            $cleanConfig[$blockName] = $this->getCleanBlock($blockConfig);
        }

        return $cleanConfig;
    }

    /**
     * Cleans section block config from internal parameters
     *
     * @param array $blockConfig
     * @return array
     */
    protected function getCleanBlock($blockConfig)
    {
        return array_intersect_key(
            $blockConfig,
            array_flip(
                [
                    'extends',
                    'config',
                ]
            )
        );
    }

    /**
     * Assemble the section config
     *
     * @throws SectionException
     */
    protected function assemble()
    {
        $this->pullExternalBlocks();
        $this->sortByNestedLevel();
        $this->processInheritance();
        $this->applyPlaceholderValues();
        $this->filterPseudoBlocks();
    }

    /**
     * Pulls external section blocks to current config
     *
     * @throws SectionException
     */
    protected function pullExternalBlocks()
    {
        $externalInheritanceBlocks = $this->getExternalInheritanceBlocks($this->configName);

        /*var_dump('$externalInheritanceBlocks');
        var_dump($externalInheritanceBlocks);*/

        foreach ($externalInheritanceBlocks as $blockName => $blockConfig) {
            /*var_dump('$blockName');
            var_dump($blockName);
            var_dump($blockConfig);*/

            $currentBlockConfig = $blockConfig;
            $blocksInheritanceStack = [$blockConfig['fullBlockName']];

            while ($currentBlockConfig['extends']) {
                $extendsConfig = $currentBlockConfig['extendsConfig'];
                $extends = $currentBlockConfig['extends'];

                $pulledBlockConfig = $this->configs[$extendsConfig][$extends] ?: null;

                if (!isset($pulledBlockConfig)) {
                    $supposedFullBlockName = "{$extendsConfig}@{$extends}";

                    $this->throwPullExternalBlocksException(
                        "Unknown external block reference ({$supposedFullBlockName}).",
                        $blocksInheritanceStack,
                        $supposedFullBlockName
                    );
                }

                if (in_array($pulledBlockConfig['fullBlockName'], $blocksInheritanceStack)) {
                    $this->throwPullExternalBlocksException(
                        "Circular reference detected while pulling external blocks.",
                        $blocksInheritanceStack,
                        $pulledBlockConfig['fullBlockName']
                    );
                }

                if (isset($this->configs[$this->configName][$extends])) {
                    $this->throwPullExternalBlocksException(
                        "There is a name conflict while pulling external blocks. Block '{$extends}' already exists.",
                        $blocksInheritanceStack,
                        $pulledBlockConfig['fullBlockName']
                    );
                }

                $blocksInheritanceStack[] = $pulledBlockConfig['fullBlockName'];

                $this->configs[$this->configName][$extends] = $pulledBlockConfig;

                $currentBlockConfig = $pulledBlockConfig;
            }
        }
    }

    /**
     * @param string $message
     * @param array $blocksInheritanceStack
     * @param null|string $supposedFullBlockName
     * @throws SectionException
     */
    protected function throwPullExternalBlocksException(
        $message,
        $blocksInheritanceStack,
        $supposedFullBlockName = null
    ) {

        if (!is_null($supposedFullBlockName)) {
            $blocksInheritanceStack[] = $supposedFullBlockName;
        }

        $message .= ' Inheritance path: ' . implode(' -> ', $blocksInheritanceStack);
        $this->throwSectionException($message);
    }

    /**
     * Sorts blocks of the current config according to the inheritance
     * @throws SectionException
     */
    protected function sortByNestedLevel()
    {
        foreach ($this->configs[$this->configName] as $blockName => $blockConfig) {
            if (isset($this->configs[$this->configName][$blockName]['nestedLevel'])) {
                continue;
            }

            $inheritancePath = $this->makeBlockInheritancePath($blockConfig);
            $this->setNestedLevel($inheritancePath);
        }

        uksort(
            $this->configs[$this->configName],
            function ($aBlockName, $bBlockName) {
                $a = $this->configs[$this->configName][$aBlockName]['nestedLevel'];
                $b = $this->configs[$this->configName][$bBlockName]['nestedLevel'];

                if ($a == $b) {
                    return 0;
                }
                return ($a < $b) ? -1 : 1;
            }
        );
    }

    /**
     * Returns an array of block names sorted according to inheritance
     *
     * @param array $blockConfig
     * @return array
     * @throws SectionException
     */
    protected function makeBlockInheritancePath($blockConfig)
    {
        $inheritancePath = [$blockConfig['name']];
        $parentBlocks = $this->getParentBlocks($blockConfig);
        $inheritancePath = array_merge(
            $inheritancePath,
            array_column($parentBlocks, 'name')
        );

        return $inheritancePath;

        /*$inheritancePath = [$blockName];
        while (isset($blockConfig['extends'])) {
            if (!isset($this->config[$this->configName][$blockConfig['extends']])) {
                throw new \Exception("Unknown parent block '{$blockConfig['extends']}'");
            }
            $inheritancePath[] = $blockConfig['extends'];

            $blockConfig = $this->config[$this->configName][$blockConfig['extends']];
        }*/

//        return $inheritancePath;
    }

    /**
     * Sets the level of nesting of blocks
     *
     * @param array $inheritancePath
     */
    protected function setNestedLevel($inheritancePath)
    {
        $nestedLevel = 1;
        $blockName = array_pop($inheritancePath);
        while (!is_null($blockName)) {
            $this->configs[$this->configName][$blockName]['nestedLevel'] = $nestedLevel++;
            $blockName = array_pop($inheritancePath);
        }
    }

    /**
     * @throws SectionException
     */
    protected function processInheritance()
    {
        foreach ($this->configs[$this->configName] as $blockName => $blockConfig) {
            /*var_dump('$blockName');
            var_dump($blockName);
            var_dump('$blockConfig');
            var_dump($blockConfig);*/

            $blockConfig = $this->processSphinxConfigInheritance($blockConfig);

            $blockConfig = $this->processPseudoBlockInheritance($blockConfig);

            $this->configs[$this->configName][$blockName] = $blockConfig;
//            $parentBlockConfig = $blockConfig;

            /*var_dump('PROCESSED BLOCK CONFIG');
            var_dump($this->config[$this->configName][$blockName]);
            var_dump('------------------------------------------------------------------');
            var_dump('');
            var_dump('');*/
        }
    }


    /**
     * Handles multi-value parameters inheritance
     *
     * @param array $blockConfig
     * @return array
     * @throws SectionException
     */
    protected function processSphinxConfigInheritance($blockConfig)
    {
        foreach ($blockConfig['config'] as $paramName => $paramValue) {
            $paramModifier = $blockConfig['paramModifier'][$paramName] ?: null;

            /*var_dump('$paramName');
            var_dump($paramName);
            var_dump('$paramModifier');
            var_dump($paramModifier);
            var_dump('$paramValue');
            var_dump($paramValue);*/

            if (
                $this->isMultiValueParam($paramName) &&
                $paramModifier != 'clear'
            ) {
                $parentParamValue = $this->getClosestParentParamValue($paramName, $blockConfig);

                /*var_dump('$parentParamValue');
                var_dump($parentParamValue);*/

                if (!is_null($parentParamValue)) {
                    $blockConfig['config'][$paramName] = $this->processSphinxParamInheritance(
                        $paramValue,
                        $parentParamValue
                    );
                }
            }

            /*var_dump('PROCESSED $paramValue');
            var_dump($blockConfig['config'][$paramName]);*/
        }
        return $blockConfig;
    }


    /**
     * Handles multi-value parameter inheritance
     *
     * @param array $paramValue
     * @param array $parentParamValue
     * @return array
     */
    protected function processSphinxParamInheritance($paramValue, $parentParamValue)
    {
        $namedValues = array_filter(
            $paramValue,
            function ($key) {
                return !is_numeric($key);
            },
            ARRAY_FILTER_USE_KEY
        );

        foreach ($namedValues as $valueName => $namedValue) {
            if (isset($parentParamValue[$valueName])) {
                $parentParamValue[$valueName] = $namedValue;
                unset($paramValue[$valueName]);
            }
        }

        $paramValue = array_merge(
            $parentParamValue,
            $paramValue
        );
        return $paramValue;
    }

    /**
     * Handles pseudo blocks parameters inheritance
     *
     * @param array $blockConfig
     * @return array
     * @throws SectionException
     */
    protected function processPseudoBlockInheritance($blockConfig)
    {
        $parentBlockConfig = $this->getParentBlock($blockConfig);

        /*var_dump('$parentBlockConfig');
        var_dump($parentBlockConfig);*/

        if (
            !is_null($parentBlockConfig) &&
            $parentBlockConfig['isPseudo']
        ) {
            $blockConfig['config'] = array_replace(
                $parentBlockConfig['config'],
                $blockConfig['config']
            );
        }
        return $blockConfig;
    }

    /**
     * @throws SectionException
     */
    protected function applyPlaceholderValues()
    {
        /*$this->placeholders = [
            'path' => [
                'to' => [
                    'value' => 'PTValue',
                ],
            ],
        ];*/

        foreach ($this->configs[$this->configName] as $blockName => $blockConfig) {
            $parentBlockConfig = $this->getParentBlock($blockConfig);

            if (!is_null($parentBlockConfig)) {
                $blockConfig['placeholderValues'] = array_replace_recursive(
                    $parentBlockConfig['placeholderValues'],
                    $blockConfig['placeholderValues']
                );
            }

            foreach ($blockConfig['config'] as $paramName => $paramValue) {
                $paramValue = (array)$paramValue;

                foreach ($paramValue as $paramKey => $currentValue) {
                    $placeholders = $this->parsePlaceholders($currentValue);

                    /*var_dump('$placeholders');
                    var_dump($placeholders);*/

                    foreach ($placeholders as $placeholder) {
                        /*var_dump('$placeholder');
                        var_dump($placeholder);*/


                        $placeholderValue = $this->findByPath(
                            array_replace_recursive(
                                $this->sphinxConfig->getPlaceholderValues(),
                                $blockConfig['placeholderValues']
                            ),
                            $this->clearPlaceholderBoundaries($placeholder)
                        );

                        /*var_dump('$placeholderValue');
                        var_dump($placeholderValue);
                        var_dump('getPlaceholderValues');
                        var_dump($this->sphinxConfig->getPlaceholderValues());
                        var_dump('REPLACE');
                        var_dump(array_replace_recursive(
                            $this->sphinxConfig->getPlaceholderValues(),
                            $blockConfig['placeholderValues']
                        ));*/

                        /*$placeholderValue = $this->findByPath(
                            $blockConfig['placeholders'],
                            $this->clearPlaceholderBoundaries($placeholder)
                        );

                        if (is_null($placeholderValue)) {
                            $placeholderValue = $this->findByPath(
                                $this->sphinxConfig->getPlaceholders(),
                                $this->clearPlaceholderBoundaries($placeholder)
                            );
                        }*/

                        $currentValue = str_replace($placeholder, (string)$placeholderValue, $currentValue);
                    }

                    $paramValue[$paramKey] = $currentValue;
                }

                if (!$this->isMultiValueParam($paramName)) {
                    $paramValue = array_pop($paramValue);
                }

                $blockConfig['config'][$paramName] = $paramValue;
            }

            $this->configs[$this->configName][$blockName] = $blockConfig;
        }
    }

    /**
     * Returns placeholder name without boundaries
     *
     * @param string $placeholderWithBoundaries
     * @return string
     */
    protected function clearPlaceholderBoundaries($placeholderWithBoundaries)
    {
        $placeholderWithBoundaries = preg_replace('/^::/', '', $placeholderWithBoundaries);
        $placeholderWithBoundaries = preg_replace('/::$/', '', $placeholderWithBoundaries);

        return $placeholderWithBoundaries;
    }

    /**
     * Returns an array value found by dot separated path
     *
     * @param array $array
     * @param string $keyPath
     * @return null|array
     */
    protected function findByPath($array, $keyPath)
    {
        $keyPathParts = explode('.', $keyPath);

        foreach ($keyPathParts as $key) {
            if (array_key_exists($key, $array)) {
                $array = $array[$key];
            } else {
                return null;
            }
        }

        return $array;
    }

    /**
     * Returns value placeholders list
     *
     * @param string $value
     * @return array
     */
    protected function parsePlaceholders($value)
    {
        preg_match_all('/::(?:.+?)(?=::)::/', $value, $m);

        return $m[0];
    }

    /**
     * Removes pseudo blocks
     * @throws SectionException
     */
    protected function filterPseudoBlocks()
    {
        $realBlocks = array_filter(
            $this->configs[$this->configName],
            function ($blockConfig) {
                return !$blockConfig['isPseudo'];
            }
        );

        foreach ($realBlocks as $blockName => $blockConfig) {

            /*var_dump('$blockName');
            var_dump($blockName);
            var_dump('$blockConfig');
            var_dump($blockConfig);*/

            $closestParentRealBlockName = $this->getClosestParentRealBlockName($blockConfig);

            /*var_dump('$closestParentRealBlockName');
            var_dump($closestParentRealBlockName);*/

            if (!is_null($closestParentRealBlockName)) {
                $blockConfig['extends'] = $closestParentRealBlockName;
            } else {
                unset(
                    $blockConfig['extends'],
                    $blockConfig['extendsConfig']
                );
            }

            $realBlocks[$blockName] = $blockConfig;
        }

        $this->configs[$this->configName] = $realBlocks;
    }

    /**
     * Returns the closest parent block for the specified block that is not a pseudo block
     *
     * @param array $blockConfig
     * @return null|string
     * @throws SectionException
     */
    protected function getClosestParentRealBlockName($blockConfig)
    {
        $parentBlocks = $this->getParentBlocks($blockConfig);

        foreach ($parentBlocks as $parentBlockConfig) {
            if (!$parentBlockConfig['isPseudo']) {
                return $parentBlockConfig['name'];
            }
        }

        return null;
    }

    /**
     * Returns the parent block for the specified block
     *
     * @param array $blockConfig
     * @return array|null
     * @throws SectionException
     */
    protected function getParentBlock($blockConfig)
    {
        $parentBlocks = $this->getParentBlocks($blockConfig);

        if (empty($parentBlocks)) {
            return null;
        }

        return array_shift($parentBlocks);
    }

    /**
     * Returns all parent blocks for the specified block
     *
     * @param array $blockConfig
     * @return array
     * @throws SectionException
     */
    protected function getParentBlocks($blockConfig)
    {
        $parentBlocks = [];
        while (isset($blockConfig['extends'])) {
            if (!isset($this->configs[$this->configName][$blockConfig['extends']])) {
                $this->throwSectionException("Unknown parent block '{$blockConfig['extends']}'");
            }

            $parentBlockConfig = $this->configs[$this->configName][$blockConfig['extends']];

            $parentBlocks[$parentBlockConfig['name']] = $parentBlockConfig;
            $blockConfig = $parentBlockConfig;
        }

        return $parentBlocks;
    }

    /**
     * Returns the value of the parameter found in the nearest parent block relative to the specified block
     *
     * @param string $paramName
     * @param array $blockConfig
     * @return null|string|array
     * @throws SectionException
     */
    protected function getClosestParentParamValue($paramName, $blockConfig)
    {
        $parentBlocks = $this->getParentBlocks($blockConfig);

        foreach ($parentBlocks as $parentBlockConfig) {
            if (isset($parentBlockConfig['config'][$paramName])) {
                return $parentBlockConfig['config'][$paramName];
            }
        }

        return null;
    }

    /**
     * Loads the specified configuration from the source
     *
     * @param string $configName
     */
    protected function loadConfig($configName)
    {
        $this->configs[$configName] = $this->loadMainConfig($configName);
        $this->configs[$configName] = array_replace(
            $this->configs[$configName],
            $this->loadConfigBlocks($configName)
        );

        $this->configs[$configName] = (array)$this->configs[$configName];

        $this->normalizeConfig($configName);
        $this->loadExternalConfigs($configName);
    }

    /**
     * @param string $configName
     */
    protected function normalizeConfig($configName)
    {
        foreach ($this->configs[$configName] as $blockName => $blockConfig) {
            $blockConfig = (array)$blockConfig;

            if (isset($blockConfig['extends'])) {
                $extendParts = explode('@', $blockConfig['extends']);
                if (isset($extendParts[1])) {
                    $blockConfig['extendsConfig'] = $extendParts[0];
                    $blockConfig['extends'] = $extendParts[1];
                } else {
                    $blockConfig['extendsConfig'] = $this->configName;
                }
            }

            $blockConfig['config'] = array_diff_key(
                $blockConfig,
                array_flip($this->getSystemParamsList())
            );

            $blockConfig = array_intersect_key(
                $blockConfig,
                array_flip($this->getSystemParamsList())
            );


            $blockConfig['name'] = $blockName;
            $blockConfig['fullBlockName'] = $configName . '@' . $blockName;
            $blockConfig['isPseudo'] = (bool)($blockConfig['isPseudo'] ?: false);
            $blockConfig['placeholderValues'] = (array)($blockConfig['placeholderValues'] ?: []);

            $this->configs[$configName][$blockName] = $this->normalizeSphinxConfig($blockConfig);
        }
    }

    /**
     * @param array $blockConfig
     * @return array
     */
    protected function normalizeSphinxConfig($blockConfig)
    {
        $blockConfig['paramModifier'] = [];
        $normalizedConfig = [];

        foreach ($blockConfig['config'] as $paramName => $paramValue) {
            list($paramName, $paramModifier) = $this->parseParamName($paramName);

            if (!is_null($paramModifier)) {
                $blockConfig['paramModifier'][$paramName] = $paramModifier;
            }

            if ($this->isMultiValueParam($paramName)) {
                $paramValue = (array)$paramValue;
            }

            $normalizedConfig[$paramName] = $paramValue;
        }

        $blockConfig['config'] = $normalizedConfig;

        return $blockConfig;
    }

    /**
     * Returns the name of the parameter and its modifier
     *
     * @param string $paramName
     * @return array
     */
    protected function parseParamName($paramName)
    {
        $paramNameParts = explode(':', $paramName);
        return [
            $paramNameParts[0],
            $paramNameParts[1] ?: null,
        ];
    }

    /**
     * Determines whether the parameter is multi-value
     *
     * @param string $paramName
     * @return bool
     */
    protected function isMultiValueParam($paramName)
    {
        return in_array(
            $paramName,
            $this->multiValueParamList
        );
    }

    /**
     * Used at a specific stage to separate the sphinx parameters from others
     *
     * @return array
     */
    protected function getSystemParamsList()
    {
        return $this->systemParamsList;
    }

    /**
     * Returns a list of blocks that inherit from external config
     *
     * @param string $configName
     * @return array
     */
    protected function getExternalInheritanceBlocks($configName)
    {
        /*var_dump('getExternalInheritanceBlocks');
        var_dump($this->config[$configName]);
        var_dump('------');*/

        return array_filter(
            $this->configs[$configName],
            function ($blockConfig) {
                return isset($blockConfig['extendsConfig']) && $blockConfig['extendsConfig'] != $this->configName;
            }
        );
    }


    /**
     * Load config from blocks of which the specified config is inherited
     *
     * @param string $configName
     */
    protected function loadExternalConfigs($configName)
    {
        $externalConfigs = $this->getExternalConfigNames($configName);

        foreach ($externalConfigs as $externalConfigName) {
            if (!isset($this->configs[$externalConfigName])) {
                $this->loadConfig($externalConfigName);
            }
        }
    }

    /**
     * Returns the list of config names from blocks of which the specified config is inherited
     *
     * @param string $configName
     * @return array
     */
    protected function getExternalConfigNames($configName)
    {
        return array_unique(
            array_column(
                $this->getExternalInheritanceBlocks($configName),
                'extendsConfig'
            )
        );
    }

    /**
     * @param string $configName
     * @return array
     */
    protected function loadMainConfig($configName)
    {
        return $this->source->load($configName, $this->sectionName);
    }

    /**
     * @param string $configName
     * @return array
     */
    protected function loadConfigBlocks($configName)
    {
        return $this->source->loadBlocks($configName, $this->sectionName);
    }

    /**
     * @param string $message
     * @throws SectionException
     */
    protected function throwSectionException($message)
    {
        throw new SectionException("An error occurred in '{$this->sectionName}' section. {$message}");
    }
}