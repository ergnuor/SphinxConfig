<?php

namespace Ergnuor\SphinxConfig;

use Ergnuor\SphinxConfig\Exception\WriterException;
use Ergnuor\SphinxConfig\Exception\SourceException;

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
    private $srcPath = null;

    /**
     * The path to the directory for the resulting config
     *
     * @var null|string
     */
    private $dstPath = null;

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
     */
    public function getSrcPath()
    {
        $curSrcPath = $this->srcPath ?: self::$defaultSrcPath;

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
     */
    public function getDstPath()
    {
        $curDstPath = $this->dstPath ?: self::$defaultDstPath;

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
     * Specifies the object to load the source config
     *
     * @return Section\SourceInterface
     * @throws SourceException
     */
    protected function getSectionSourceObject()
    {
        return new Section\Source\PhpArray($this->getSrcPath());
    }

    /**
     * Specifies the object to generate the resulting configs
     *
     * @return Section\Writer\AdapterInterface
     * @throws WriterException
     */
    protected function getWriterAdapterObject()
    {
        $dstPath = $this->getDstPath();

        if (empty($dstPath)) {
            return new Section\Writer\Adapter\SphinxConfigStdout();
        } else {
            return new Section\Writer\Adapter\SphinxConfigFile($dstPath);
        }
    }
}