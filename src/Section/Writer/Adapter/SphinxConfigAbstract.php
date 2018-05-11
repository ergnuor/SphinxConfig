<?php

namespace Ergnuor\SphinxConfig\Section\Writer\Adapter;

use Ergnuor\SphinxConfig\Section\Writer\AdapterInterface;
use Ergnuor\SphinxConfig\Exception\WriterException;

abstract class SphinxConfigAbstract implements AdapterInterface
{
    /**
     * @var string
     */
    protected $buffer = '';

    /**
     * Method called before the formation of sections
     *
     * @param string $configName
     * @throws WriterException
     */
    public function beforeWriteConfig($configName)
    {
        $this->buffer = '';
    }

    /**
     * Method called after the formation of sections
     *
     * @param string $configName
     * @throws WriterException
     */
    abstract public function afterWriteConfig($configName);

    /**
     * @param string $blockName
     * @param null|string $extends
     * @param null|string $sectionName
     */
    public function startBlock($blockName, $extends = null, $sectionName = null)
    {
        $blockStartText = $sectionName ? $sectionName . ' ' : '';
        $blockStartText .= $blockName;
        if (isset($extends)) {
            $blockStartText .= ' : ' . $extends;
        }

        $this->buffer .= "{$blockStartText} {" . PHP_EOL;
    }

    public function endBlock()
    {
        $this->buffer .= "}" . PHP_EOL . PHP_EOL;
    }

    /**
     * @param string $paramName
     * @param string $paramValue
     */
    public function writeParam($paramName, $paramValue)
    {
        $this->buffer .= "\t{$paramName} = $paramValue" . PHP_EOL;
    }
}