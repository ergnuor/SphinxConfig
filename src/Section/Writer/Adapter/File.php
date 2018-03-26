<?php

namespace Ergnuor\SphinxConfig\Section\Writer\Adapter;

use Ergnuor\SphinxConfig\Section\Writer\AdapterInterface;
use Ergnuor\SphinxConfig\Exception\WriterException;

class File implements AdapterInterface
{
    protected $handler = null;

    /**
     * Directory to which the config file will be saved
     *
     * @var null|string
     */
    protected $path = null;

    /**
     * Path to the temporary file used to generate the config
     *
     * @var null|string
     */
    protected $tmpConfigFilePath = null;

    /**
     * Sets the directory in which the config file will be saved
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = rtrim((string)$path);
        $this->path = rtrim((string)$this->path, DIRECTORY_SEPARATOR);
    }

    /**
     * Method called before the formation of sections
     *
     * @param string $configName
     * @throws WriterException
     */
    public function beforeWriteConfig($configName)
    {
        if (!file_exists($this->path)) {
            throw new WriterException("Directory '{$this->path}' does not exists");
        }

        $this->tmpConfigFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR .
            "sphinxConfig{$configName}_" . md5(uniqid("sphinxConfig{$configName}", true)) . '.conf';
        $this->handler = fopen($this->tmpConfigFilePath, "w");

        if ($this->handler === false) {
            throw new WriterException("Could not create temporary config file '{$this->tmpConfigFilePath}'");
        }

        fwrite($this->handler, '');
    }

    /**
     * Method called after the formation of sections
     *
     * @param string $configName
     */
    public function afterWriteConfig($configName)
    {
        fclose($this->handler);
        copy($this->tmpConfigFilePath, $this->path . DIRECTORY_SEPARATOR . $configName . '.conf');
        unlink($this->tmpConfigFilePath);
    }

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

        fwrite($this->handler, "{$blockStartText} {\n");
    }

    public function endBlock()
    {
        fwrite($this->handler, "}\n\n");
    }

    /**
     * @param string $paramName
     * @param string $paramValue
     */
    public function writeParam($paramName, $paramValue)
    {
        fwrite($this->handler, "\t{$paramName} = $paramValue\n");
    }
}