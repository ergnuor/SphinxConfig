<?php

namespace Ergnuor\SphinxConfig;


use \Ergnuor\SphinxConfig\Exception\SectionException;

class Section
{
    /**
     * Contains the current and external configurations of the section
     *
     * @var array
     */
    private $configs = [];

    /**
     * Contains current config name
     *
     * @var string
     */
    private $configName;

    /**
     * @var string
     */
    private $sectionName;

    /**
     * @var array
     */
    private $globalPlaceholderValues = [];

    /**
     * @var array
     */
    private $multiValueParamList = [
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
    private $systemParamsList = [
        'extends',
        'extendsConfig',
        'config',
        'placeholderValues',
        'isPseudo',
    ];

    /**
     * @var Section\Reader
     */
    private $reader;

    /**
     * @var Section\Writer
     */
    private $writer;

    /**
     * @param string $configName
     * @param string $sectionName
     * @param Section\Reader\Adapter $readerAdapter
     * @param Section\Writer\Adapter $writerAdapter
     * @param array $globalPlaceholderValues
     */
    public function __construct(
        $configName,
        $sectionName,
        Section\Reader\Adapter $readerAdapter,
        Section\Writer\Adapter $writerAdapter,
        array $globalPlaceholderValues
    )
    {
        $this->configName = (string)$configName;
        $this->sectionName = (string)$sectionName;
        $this->globalPlaceholderValues = $globalPlaceholderValues;

        $this->createEndpoints($readerAdapter, $writerAdapter);
    }

    private function createEndpoints(
        Section\Reader\Adapter $readerAdapter,
        Section\Writer\Adapter $writerAdapter
    )
    {
        $this->reader = new Section\Reader($readerAdapter);
        $this->writer = new Section\Writer($writerAdapter);
    }

    public function getConfigName()
    {
        return $this->configName;
    }

    public function transform()
    {
        $this->readConfig($this->configName);
        $this->assemble();
        $this->writer->write($this->getConfig(), $this);
    }

    /**
     * @param string $configName
     */
    private function readConfig($configName)
    {
        $this->configs[$configName] = $this->reader->readConfig($configName, $this);

        $this->normalizeConfig($configName);
        $this->readExternalConfigs($configName);
    }

    /**
     * @param string $configName
     */
    private function normalizeConfig($configName)
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

            $blockConfig = $this->removeCustomParams($blockConfig);

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
            $blockConfig['isPseudo'] = (bool)(array_key_exists('isPseudo', $blockConfig) ?
                $blockConfig['isPseudo'] : false);
            $blockConfig['placeholderValues'] = isset($blockConfig['placeholderValues']) ?
                (array)$blockConfig['placeholderValues'] : [];

            $this->configs[$configName][$blockName] = $this->normalizeSphinxConfig($blockConfig);
        }
    }

    private function removeCustomParams($blockConfig)
    {
        return array_filter(
            $blockConfig,
            function ($k) {
                return $k[0] != '_';
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @return array
     */
    private function getSystemParamsList()
    {
        return $this->systemParamsList;
    }

    /**
     * @param array $blockConfig
     * @return array
     */
    private function normalizeSphinxConfig($blockConfig)
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
    private function parseParamName($paramName)
    {
        $paramNameParts = explode(':', $paramName);
        return [
            $paramNameParts[0],
            isset($paramNameParts[1]) ? $paramNameParts[1] : null,
        ];
    }

    /**
     * @param string $paramName
     * @return bool
     */
    private function isMultiValueParam($paramName)
    {
        return in_array(
            $paramName,
            $this->multiValueParamList
        );
    }

    /**
     * @param string $configName
     */
    private function readExternalConfigs($configName)
    {
        $externalConfigs = $this->getExternalConfigNames($configName);

        foreach ($externalConfigs as $externalConfigName) {
            if (!isset($this->configs[$externalConfigName])) {
                $this->readConfig($externalConfigName);
            }
        }
    }

    /**
     * @param string $configName
     * @return array
     */
    private function getExternalConfigNames($configName)
    {
        return array_unique(
            array_column(
                $this->getExternalInheritanceBlocks($configName),
                'extendsConfig'
            )
        );
    }

    /**
     * Returns a list of blocks that inherit from external config
     *
     * @param string $configName
     * @return array
     */
    private function getExternalInheritanceBlocks($configName)
    {
        return array_filter(
            $this->configs[$configName],
            function ($blockConfig) {
                return isset($blockConfig['extendsConfig']) && $blockConfig['extendsConfig'] != $this->configName;
            }
        );
    }

    /**
     * @throws SectionException
     */
    private function assemble()
    {
        $this->pullExternalBlocks();
        $this->sortByInheritanceLevel();
        $this->processInheritance();
        $this->processParamsValues();
        $this->filterPseudoBlocks();
    }

    /**
     * @throws SectionException
     */
    private function pullExternalBlocks()
    {
        $externalInheritanceBlocks = $this->getExternalInheritanceBlocks($this->configName);

        $pulledBlocks = [];

        foreach ($externalInheritanceBlocks as $blockName => $blockConfig) {
            $currentBlockConfig = $blockConfig;
            $blocksInheritanceStack = [$blockConfig['fullBlockName']];

            while (isset($currentBlockConfig['extends'])) {
                $extendsConfig = $currentBlockConfig['extendsConfig'];
                $extends = $currentBlockConfig['extends'];

                $pulledBlockConfig = isset($this->configs[$extendsConfig][$extends]) ?
                    $this->configs[$extendsConfig][$extends] : null;

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

                if (in_array($pulledBlockConfig['fullBlockName'], $pulledBlocks)) {
                    break;
                }

                if (isset($this->configs[$this->configName][$extends])) {
                    $this->throwPullExternalBlocksException(
                        "There is a name conflict while pulling external blocks. Block '{$extends}' already exists.",
                        $blocksInheritanceStack,
                        $pulledBlockConfig['fullBlockName']
                    );
                }

                $blocksInheritanceStack[] = $pulledBlockConfig['fullBlockName'];
                $pulledBlocks[] = $pulledBlockConfig['fullBlockName'];

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
    private function throwPullExternalBlocksException(
        $message,
        $blocksInheritanceStack,
        $supposedFullBlockName = null
    )
    {

        if (!is_null($supposedFullBlockName)) {
            $blocksInheritanceStack[] = $supposedFullBlockName;
        }

        $message .= ' Inheritance path: ' . implode(' -> ', $blocksInheritanceStack);
        $this->throwSectionException($message);
    }

    /**
     * @param string $message
     * @throws SectionException
     */
    private function throwSectionException($message)
    {
        throw new SectionException("An error occurred in '{$this->sectionName}' section. {$message}");
    }

    /**
     * @throws SectionException
     */
    private function sortByInheritanceLevel()
    {
        foreach ($this->configs[$this->configName] as $blockName => $blockConfig) {
            if (isset($this->configs[$this->configName][$blockName]['inheritanceLevel'])) {
                continue;
            }

            $inheritancePath = $this->getBlocksInInheritanceOrder($blockConfig);
            $this->setInheritanceLevel($inheritancePath);
        }

        uksort(
            $this->configs[$this->configName],
            function ($aBlockName, $bBlockName) {
                $a = $this->configs[$this->configName][$aBlockName]['inheritanceLevel'];
                $b = $this->configs[$this->configName][$bBlockName]['inheritanceLevel'];

                if ($a == $b) {
                    return strcmp($aBlockName, $bBlockName);
                }
                return ($a < $b) ? -1 : 1;
            }
        );
    }

    /**
     * @param array $blockConfig
     * @return array
     * @throws SectionException
     */
    private function getBlocksInInheritanceOrder($blockConfig)
    {
        $inheritancePath = [$blockConfig['name']];
        $parentBlocks = $this->getParentBlocks($blockConfig);
        $inheritancePath = array_merge(
            $inheritancePath,
            array_column($parentBlocks, 'name')
        );

        return $inheritancePath;
    }

    /**
     * @param array $blockConfig
     * @return array
     * @throws SectionException
     */
    private function getParentBlocks($blockConfig)
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
     * @param array $inheritancePath
     */
    private function setInheritanceLevel($inheritancePath)
    {
        $inheritanceLevel = 1;
        $blockName = array_pop($inheritancePath);
        while (!is_null($blockName)) {
            $this->configs[$this->configName][$blockName]['inheritanceLevel'] = $inheritanceLevel++;
            $blockName = array_pop($inheritancePath);
        }
    }

    /**
     * @throws SectionException
     */
    private function processInheritance()
    {
        foreach ($this->configs[$this->configName] as $blockName => $blockConfig) {
            $blockConfig = $this->processMultiValueParamsInheritance($blockConfig);
            $blockConfig = $this->processPseudoBlockInheritance($blockConfig);
            $blockConfig = $this->propagatePlaceholderValues($blockConfig);

            $this->configs[$this->configName][$blockName] = $blockConfig;
        }
    }

    /**
     * @param array $blockConfig
     * @return array
     * @throws SectionException
     */
    private function processMultiValueParamsInheritance($blockConfig)
    {
        foreach ($blockConfig['config'] as $paramName => $paramValue) {
            $paramModifier = isset($blockConfig['paramModifier'][$paramName]) ?
                $blockConfig['paramModifier'][$paramName] : null;

            if (
                !$this->isMultiValueParam($paramName) ||
                $paramModifier == 'clear'
            ) {
                continue;
            }

            $parentParamValue = $this->getClosestParentParamValue($paramName, $blockConfig);

            if (!is_null($parentParamValue)) {
                $blockConfig['config'][$paramName] = $this->mergeMultiValueParamValues(
                    $paramValue,
                    $parentParamValue
                );
            }
        }
        return $blockConfig;
    }

    /**
     * @param string $paramName
     * @param array $blockConfig
     * @return null|string|array
     * @throws SectionException
     */
    private function getClosestParentParamValue($paramName, $blockConfig)
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
     * @param array $paramValue
     * @param array $parentParamValue
     * @return array
     */
    private function mergeMultiValueParamValues($paramValue, $parentParamValue)
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
     * @param array $blockConfig
     * @return array
     * @throws SectionException
     */
    private function processPseudoBlockInheritance($blockConfig)
    {
        $parentBlockConfig = $this->getParentBlock($blockConfig);

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
     * @param array $blockConfig
     * @return array|null
     * @throws SectionException
     */
    private function getParentBlock($blockConfig)
    {
        $parentBlocks = $this->getParentBlocks($blockConfig);

        if (empty($parentBlocks)) {
            return null;
        }

        return array_shift($parentBlocks);
    }

    /**
     * @param $blockConfig
     * @return mixed
     * @throws SectionException
     */
    private function propagatePlaceholderValues($blockConfig)
    {
        $parentBlockConfig = $this->getParentBlock($blockConfig);

        if (!is_null($parentBlockConfig)) {
            $blockConfig['placeholderValues'] = array_replace_recursive(
                $parentBlockConfig['placeholderValues'],
                $blockConfig['placeholderValues']
            );
        }

        return $blockConfig;
    }

    /**
     * @throws SectionException
     */
    private function processParamsValues()
    {
        foreach ($this->configs[$this->configName] as $blockName => $blockConfig) {
            foreach ($blockConfig['config'] as $paramName => $paramValue) {
                $paramValue = (array)$paramValue;

                foreach ($paramValue as $paramKey => $currentValue) {
                    $currentValue = $this->applyPlaceholdersValues($currentValue, $blockConfig);
                    $currentValue = $this->processMultilineValue($currentValue);

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
     * @param $currentValue
     * @param $blockConfig
     * @param array $processedPlaceholders
     * @return mixed
     * @throws SectionException
     */

    private function applyPlaceholdersValues($currentValue, $blockConfig, $processedPlaceholders = [])
    {
        $placeholders = $this->parsePlaceholders($currentValue);

        if (empty($placeholders)) {
            return $currentValue;
        }

        if (!empty(array_intersect($placeholders, $processedPlaceholders))) {
            $this->throwSectionException(
                "Circular placeholders detected. Processed placeholders: " . implode(' ,', $processedPlaceholders)
            );
        }

        $processedPlaceholders = array_merge($processedPlaceholders, $placeholders);

        foreach ($placeholders as $placeholder) {
            $placeholderValue = $this->findByPath(
                array_replace_recursive(
                    $this->globalPlaceholderValues,
                    $blockConfig['placeholderValues']
                ),
                $this->clearPlaceholderBoundaries($placeholder)
            );

            if (is_array($placeholderValue)) {
                $placeholderValue = implode(', ', $placeholderValue);
            }

            $currentValue = str_replace($placeholder, (string)$placeholderValue, $currentValue);
        }

        return $this->applyPlaceholdersValues($currentValue, $blockConfig, $processedPlaceholders);
    }

    /**
     * @param string $value
     * @return array
     */
    private function parsePlaceholders($value)
    {
        preg_match_all('/::(?:.+?)(?=::)::/', $value, $m);

        return $m[0];
    }

    /**
     * Returns an array value found by dot separated path
     *
     * @param array $array
     * @param string $keyPath
     * @return null|array
     */
    private function findByPath($array, $keyPath)
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
     * @param string $placeholderWithBoundaries
     * @return string
     */
    private function clearPlaceholderBoundaries($placeholderWithBoundaries)
    {
        $placeholderWithBoundaries = preg_replace('/^::/', '', $placeholderWithBoundaries);
        $placeholderWithBoundaries = preg_replace('/::$/', '', $placeholderWithBoundaries);

        return $placeholderWithBoundaries;
    }

    private function processMultilineValue($value)
    {
        return preg_replace('/([\r\n]+)/', ' \\\$1', $value);
    }

    /**
     * @throws SectionException
     */
    private function filterPseudoBlocks()
    {
        $realBlocks = array_filter(
            $this->configs[$this->configName],
            function ($blockConfig) {
                return !$blockConfig['isPseudo'];
            }
        );

        foreach ($realBlocks as $blockName => $blockConfig) {
            $closestParentRealBlockName = $this->getClosestParentRealBlockName($blockConfig);

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
    private function getClosestParentRealBlockName($blockConfig)
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
     * @return array
     */
    private function getConfig()
    {
        $cleanConfig = [];
        foreach ($this->configs[$this->configName] as $blockName => $blockConfig) {
            $cleanConfig[$blockName] = $this->removeSystemParameters($blockConfig);
        }

        return $cleanConfig;
    }

    /**
     * @param array $blockConfig
     * @return array
     */
    private function removeSystemParameters($blockConfig)
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
     * @return null|string
     */
    public function getName()
    {
        return $this->sectionName;
    }
}