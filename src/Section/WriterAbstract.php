<?php

namespace Ergnuor\SphinxConfig\Section;


abstract class WriterAbstract
{
    /**
     * @var Writer\AdapterInterface|null
     */
    protected $writerAdapter = null;

    /**
     * @param Writer\AdapterInterface $writerAdapter
     */
    public function __construct(Writer\AdapterInterface $writerAdapter)
    {
        $this->writerAdapter = $writerAdapter;
    }

    /**
     * @param string $paramName
     * @param string $paramValue
     */
    protected function writeParam($paramName, $paramValue)
    {
        $paramValue = (array)$paramValue;
        foreach ($paramValue as $curParamValue) {
            $this->writerAdapter->writeParam($paramName, $curParamValue);
        }
    }

    /**
     * @param MultiBlock $section
     */
    abstract public function writeSection(MultiBlock $section);
}