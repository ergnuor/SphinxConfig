<?php

namespace Ergnuor\SphinxConfig\Section\Writer\Adapter;

use Ergnuor\SphinxConfig\Section\Writer\Adapter;
use Ergnuor\SphinxConfig\Exception\WriterException;

class NativeConfig implements Adapter
{
    /**
     * @var string
     */
    protected $buffer = '';

    /**
     * Directory to which the config file will be saved
     *
     * @var string
     */
    private $dstPath;

    /**
     * @param null|string $dstPath
     */
    public function __construct($dstPath = null)
    {
        $this->dstPath = $dstPath;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->buffer = '';
    }

    public function write($configName)
    {
        $handle = fopen($this->getFilePath($configName), 'w');
        fwrite($handle, $this->buffer);
        fclose($handle);
    }

    private function getFilePath($configName)
    {
        if (is_null($this->dstPath)) {
            return 'php://stdout';
        } else {
            if (!is_dir($this->dstPath)) {
                throw new WriterException("'{$this->dstPath}' is not a directory");
            }

            if (!is_writable($this->dstPath)) {
                throw new WriterException("Destination directory '{$this->dstPath}' is not writable");
            }

            $path = realpath($this->dstPath);
            if ($path === false) {
                throw new WriterException("Getting real path for '{$this->dstPath}' directory is failed");
            }

            return $path . DIRECTORY_SEPARATOR . $configName . '.conf';
        }
    }

    /**
     * @param string $sectionName
     * @param string $blockName
     * @param null|string $extends
     */
    public function startMultiBlockSection($sectionName, $blockName, $extends = null)
    {
        $blockStartText = "{$sectionName} {$blockName}";
        if (isset($extends)) {
            $blockStartText .= ' : ' . $extends;
        }

        $this->writeBlock($blockStartText);
    }

    private function writeBlock($fullBlockName)
    {
        $this->buffer .= "{$fullBlockName} {" . PHP_EOL;
    }

    public function endMultiBlockSection()
    {
        $this->endSection();
    }

    private function endSection()
    {
        $this->buffer .= "}" . PHP_EOL . PHP_EOL;
    }

    /**
     * @param string $sectionName
     */
    public function startSingleBlockSection($sectionName)
    {
        $this->writeBlock($sectionName);
    }

    public function endSingleBlockSection()
    {
        $this->endSection();
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