<?php

namespace Ergnuor\SphinxConfig\Section\Writer;

use Ergnuor\SphinxConfig\Section\WriterAbstract;

class SingleBlock extends WriterAbstract
{
    /**
     * @param \Ergnuor\SphinxConfig\Section\MultiBlock $section
     * @throws \Ergnuor\SphinxConfig\Exception\SectionException
     */
    public function writeSection(\Ergnuor\SphinxConfig\Section\MultiBlock $section)
    {
        $config = $section->getConfig();

        if (empty($config)) {
            return;
        }

        $sectionName = $section->getSectionName();

        $this->writerAdapter->startBlock($sectionName);
        foreach ($config as $paramName => $paramValue) {
            $this->writeParam($paramName, $paramValue);
        }
        $this->writerAdapter->endBlock();
    }
}