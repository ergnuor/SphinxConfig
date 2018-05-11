<?php

namespace Ergnuor\SphinxConfig\Section\Writer\Adapter;

use Ergnuor\SphinxConfig\Exception\WriterException;

class SphinxConfigStdout extends SphinxConfigAbstract
{
    /**
     * Method called after the formation of sections
     *
     * @param string $configName
     * @throws WriterException
     */
    public function afterWriteConfig($configName)
    {
        fwrite(STDOUT, $this->buffer);
    }
}