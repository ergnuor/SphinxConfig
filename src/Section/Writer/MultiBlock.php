<?php

namespace Ergnuor\SphinxConfig\Section\Writer;

use Ergnuor\SphinxConfig\Section\WriterAbstract;

class MultiBlock extends WriterAbstract
{
    /**
     * @param \Ergnuor\SphinxConfig\Section\MultiBlock $section
     * @throws \Ergnuor\SphinxConfig\Exception\SectionException
     */
    public function writeSection(\Ergnuor\SphinxConfig\Section\MultiBlock $section)
    {
        $config = $section->getConfig();

        foreach ($config as $blockName => $blockConfig) {
            $extends = $blockConfig['extends'] ?: null;

            $this->writerAdapter->startBlock($blockName, $extends, $section->getSectionName());
            foreach ($blockConfig['config'] as $paramName => $paramValue) {
                $this->writeParam($paramName, $paramValue);
            }
            $this->writerAdapter->endBlock();
        }
    }
}