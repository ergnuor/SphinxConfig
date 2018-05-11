<?php

namespace Ergnuor\SphinxConfig\Section\Writer\Adapter;

use Ergnuor\SphinxConfig\Exception\WriterException;

class SphinxConfigFile extends SphinxConfigAbstract
{
    /**
     * Directory to which the config file will be saved
     *
     * @var null|string
     */
    protected $dstPath = null;

    /**
     * File constructor.
     * @param $dstPath
     * @throws WriterException
     */
    public function __construct($dstPath = null)
    {
        $this->dstPath = trim((string)$dstPath);
    }

    /**
     * Method called after the formation of sections
     *
     * @param string $configName
     * @throws WriterException
     */
    public function afterWriteConfig($configName)
    {
        if (empty($this->dstPath)) {
            throw new WriterException('Destination path required');
        }

        $resultFileName = $this->dstPath . DIRECTORY_SEPARATOR . $configName . '.conf';

        if (!is_writable($resultFileName)) {
            throw new WriterException("Destination file '{$resultFileName}' is not writable");
        }


        file_put_contents(
            $resultFileName,
            $this->buffer
        );
    }
}