<?php

namespace Ergnuor\SphinxConfig;

use Ergnuor\SphinxConfig\Exception\ConfigurationException;
use MongoDB\Driver\Exception\ConnectionException;

class SphinxConfig extends SphinxConfigAbstract
{
    /**
     * The path to the directory containing the configs.
     * Used globally for all configs
     *
     * @var null|string
     */
    protected static $defaultSrcPath = null;

    /**
     * The path to the directory for the resulting config.
     * Used globally for all configs
     *
     * @var null|string
     */
    protected static $defaultDstPath = null;

    /**
     * The path to the directory containing the configs
     *
     * @var null|string
     */
    protected $srcPath = null;

    /**
     * The path to the directory for the resulting config
     *
     * @var null|string
     */
    protected $dstPath = null;

    /**
     * Sets the default path to the directory containing the configs
     *
     * @param string $srcPath
     */
    public static function setDefaultSrcPath($srcPath)
    {
        self::$defaultSrcPath = (string)$srcPath;
    }

    /**
     * Sets the default path to the directory for the resulting config
     *
     * @param string $dstPath
     */
    public static function setDefaultDstPath($dstPath)
    {
        self::$defaultDstPath = (string)$dstPath;
    }

    /**
     * @param bool $checkPath
     * @return null|string
     * @throws ConfigurationException
     */
    public function getSrcPath($checkPath = false)
    {
        $curSrcPath = $this->srcPath ?: self::$defaultSrcPath;

        if ($checkPath) {
            $this->checkPath($curSrcPath, 'source');
        }

        return $curSrcPath;
    }

    /**
     * @param string $srcPath
     * @return $this
     */
    public function setSrcPath($srcPath)
    {
        $this->srcPath = trim((string)$srcPath);
        return $this;
    }

    /**
     * @param bool $checkPath
     * @return null|string
     * @throws ConfigurationException
     */
    public function getDstPath($checkPath = false)
    {
        $curDstPath = $this->dstPath ?: self::$defaultDstPath;
        if (empty($curDstPath)) {
            $curDstPath = $this->getSrcPath();
        }

        if ($checkPath) {
            $this->checkPath($curDstPath, 'destination');
        }

        return $curDstPath;
    }

    /**
     * @param string $dstPath
     * @return $this
     */
    public function setDstPath($dstPath)
    {
        $this->dstPath = trim((string)$dstPath);
        return $this;
    }

    /**
     * @param string $path
     * @param string $type
     * @throws ConfigurationException
     */
    protected function checkPath($path, $type)
    {
        if (empty($path)) {
            throw new ConnectionException("Config {$type} path required");
        }

        $type = ucfirst($type);

        if (!file_exists($path)) {
            throw new ConfigurationException("{$type} directory '{$path}' does not exists");
        }
    }

    /**
     * Specifies the object to load the source config
     *
     * @return Section\SourceInterface
     * @throws ConfigurationException
     */
    protected function getSectionSourceObject()
    {
        return new Section\Source\File($this->getSrcPath(true));
    }

    /**
     * Specifies the object to generate the resulting configs
     *
     * @return Section\Writer\AdapterInterface
     * @throws ConfigurationException
     */
    protected function getWriterAdapterObject()
    {
        $writer = new Section\Writer\Adapter\File();
        $writer->setPath($this->getDstPath(true));
        return $writer;
    }
}